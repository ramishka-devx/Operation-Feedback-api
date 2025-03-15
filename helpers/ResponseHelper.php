<?php 
class ResponseHelper {
    public static function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
}

?>