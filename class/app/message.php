<?php
namespace app;
class message {
    /**
     * sendSocket — previously relayed real-time events to api.fullmedia.sk (ex-employee's server).
     * That external dependency has been removed. This is a no-op stub kept for API compatibility.
     * A self-hosted WebSocket solution can be added here in the future.
     */
    public function sendSocket($metoda, $data, $kluc=null){
        // No-op: external WebSocket relay removed.
        return null;
    }
}
