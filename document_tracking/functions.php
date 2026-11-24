<?php

function generateTrackingNumber() {
    return 'DOC-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

?>