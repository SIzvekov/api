<?php
$response = array(
    'code' => intval($this->OUTPUT_CODE),
    'status' => $this->OUTPUT_STATUS,
    'response' => $this->OUTPUT_RESPONSE
);
if (is_array($this->RESPONSE_PAGINATION) && sizeof($this->RESPONSE_PAGINATION)) {
    $response['pagination'] = $this->RESPONSE_PAGINATION;
}
print_r($response);