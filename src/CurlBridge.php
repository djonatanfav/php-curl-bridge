<?php

class CurlBridge
{
    /**
     * 
     */
    private string $finalURL;

    /**
     * 
     */
    private string $postFields;

    /**
     * 
     */
    private string $getFields;

    /**
     * 
     */
    private string $content;

    /**
     * 
     */
    private array  $header = array();

    /**
     * 
     */
    private string $method;

    /**
     * 
     */
    private $request;

    /**
     * 
     */
    private string $response;

    /**
     * 
     */
    public function __construct()
    {
        /** Detect method */
        $method = $_SERVER['REQUEST_METHOD']    ?? "";

        /* Run Header Loader */
        $this->loadHeader();
        
        /** Set method | Show an error if it retuns false */
        if($this->setMethod($method) === false){
            if(empty($method)){
                echo "Não foi possível identificar o método";
            } else {
                echo "Método não disponível";
            }
            /** Exit */
            http_response_code(401); // Unauthorized
            exit(0);
        }

        /** Load URL */
        if($this->loadFinalURL() === false){
            echo "URL Não identificada";
            http_response_code(400); // Bad Request
            exit(0);
        } 

        /** Load post fields */
        $this->loadPostFields();

        /** Load get fields */
        $this->loadGetFields();

        /** Load body contet */
        $this->loadContent();
    }

    /**
     * 
     */
    public function loadHeader()
    {
        /* Get all header parameters */
        $headers = apache_request_headers();

        unset(
            $headers["Content-Length"],
            $headers["Host"]
        );

        /* Foreach header properties */
        foreach ($headers as $header => $value) {
            /* Header layout */
            $stringHeader = $header . ':' . $value;

            /* Add to array  */
            array_push($this->header, $stringHeader);
        } 

    }

    /**
     * 
     */
    public function setMethod(string $method) : bool
    {
        switch($method){
            case 'POST':
                /** METHOD POST */
                $this->method = 'POST';
                return true;
                break;
            case 'GET':
                /** METHOD GET */
                $this->method = 'GET';
                return true;
                break;
            default:
                /** RETURN ERROR */
                return false;
                break;
        }
    }

    /**
     * 
     */
    public function loadFinalURL() : bool
    {
        /* Get the URL from Request */
        $this->finalURL = $_GET['curl_bridge_url']  ??  "";

        /* Checking if is empty */
        if(empty($this->finalURL)){
            return false;
        }

        /* Returns true if URL was detected */
        return true;
    }

    /**
     * 
     */
    public function loadPostFields() : void
    {
        if($this->method === 'POST'){
            /* Build Query */
            if(count($_POST) > 0){
                $this->postFields = http_build_query($_POST);
            } else {
                $this->postFields = '';
            }
        }
    }

    /**
     * 
     */
    public function loadGetFields() : void
    {
        if($this->method === 'GET'){
            /* Build Query */
            if(count($_GET) > 0){
                $this->getFields  = http_build_query($_GET);
                $this->finalURL  .= $this->getFields;
            } else {
                $this->getFields = '';
            }
        }
    }

    /**
     * 
     */
    public function loadContent() : void
    {
        $this->body = file_get_contents('php://input');
    }

    /**
     * 
     */
    public function runRequest() : void
    {
        /** Start CURL in an attribute */
        $this->request = curl_init();

        /** URL */
        curl_setopt($this->request,         CURLOPT_URL             , $this->finalURL);

        /** RETURN TRANSFER AND HEADERS */
        curl_setopt($this->request,         CURLOPT_RETURNTRANSFER  , true);
        curl_setopt($this->request,         CURLOPT_HEADER          , true);

        /** SET HEADER */
        curl_setopt($this->request,         CURLOPT_HTTPHEADER      , $this->header);

        if($this->method === 'POST'){
            /** SET POST */
            curl_setopt($this->request,     CURLOPT_POST            , true);

            // echo $this->typeOfRequest();

            if($this->typeOfRequest() != 3){
                curl_setopt($this->request, CURLOPT_POSTFIELDS      , $this->body);
            } else {
                curl_setopt($this->request, CURLOPT_POSTFIELDS      , $this->postFields);
            }
        }

        $this->response = curl_exec($this->request);
    }

    /**
     * 
     */
    public function typeOfRequest() : int
    {
        if($this->method === 'GET'){
            return 1;
        } else if ($this->method === 'POST' && $this->postFields === ''){
            return 2;
        } else {
            return 3;
        }

        return 0;
    }

    /**
     * 
     */
    public function sendResponse() : void
    {
        /** HTTP Status Code */
        http_response_code(curl_getinfo($this->request, CURLINFO_HTTP_CODE));

        /** Header Size */
        $header_size = curl_getinfo($this->request, CURLINFO_HEADER_SIZE);

        /** Montar Header */
        foreach(explode('<br />', nl2br(substr($this->response, 0, $header_size))) as $key => $value){
            header(trim($value));
        }

        /** Body */
        echo substr($this->response, $header_size);
        
        exit(0);
    }
}