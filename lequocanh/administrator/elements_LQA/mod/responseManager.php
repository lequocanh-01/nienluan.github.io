<?php
/**
 * Response Manager Class
 * Handles HTTP responses safely, preventing header conflicts
 */
class ResponseManager {
    
    /**
     * Safe redirect with fallback to JavaScript
     */
    public static function redirect($url, $statusCode = 302) {
        if (headers_sent($file, $line)) {
            Logger::warning('Headers already sent, using JavaScript redirect', [
                'url' => $url,
                'file' => $file,
                'line' => $line
            ]);
            
            // Fallback to JavaScript redirect
            echo "<script type='text/javascript'>";
            echo "window.location.href = '" . addslashes($url) . "';";
            echo "</script>";
            echo "<noscript>";
            echo "<meta http-equiv='refresh' content='0;url=" . htmlspecialchars($url) . "'>";
            echo "</noscript>";
            return;
        }
        
        try {
            http_response_code($statusCode);
            header("Location: $url");
            exit();
        } catch (Exception $e) {
            Logger::error('Redirect failed', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Send JSON response safely
     */
    public static function json($data, $statusCode = 200) {
        if (headers_sent($file, $line)) {
            Logger::error('Cannot send JSON response, headers already sent', [
                'file' => $file,
                'line' => $line
            ]);
            return false;
        }
        
        try {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            exit();
        } catch (Exception $e) {
            Logger::error('JSON response failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Send success JSON response
     */
    public static function success($message = 'Success', $data = null) {
        $response = ['success' => true, 'message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }
        self::json($response);
    }
    
    /**
     * Send error JSON response
     */
    public static function error($message = 'Error', $statusCode = 400, $data = null) {
        $response = ['success' => false, 'message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }
        self::json($response, $statusCode);
    }
    
    /**
     * Set HTTP status code safely
     */
    public static function setStatusCode($code) {
        if (!headers_sent()) {
            http_response_code($code);
            return true;
        }
        return false;
    }
    
    /**
     * Set header safely
     */
    public static function setHeader($name, $value) {
        if (!headers_sent()) {
            header("$name: $value");
            return true;
        }
        Logger::warning('Cannot set header, headers already sent', [
            'header' => $name,
            'value' => $value
        ]);
        return false;
    }
}