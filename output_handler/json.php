<?php
$response = array(
    'code' => intval($this->OUTPUT_CODE),
    'status' => $this->OUTPUT_STATUS,
    'response' => $this->json_numeric_check($this->OUTPUT_RESPONSE)
);
if (is_array($this->RESPONSE_PAGINATION) && sizeof($this->RESPONSE_PAGINATION)) {
    $response['pagination'] = $this->RESPONSE_PAGINATION;
}
echo json_encode($response,
    JSON_BIGINT_AS_STRING | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
/* note: 
		JSON_NUMERIC_CHECK causes issues when a "string" value is all numbers and long 
        	eg "23489234982734897982734897" will get converted to a float like 2.3489234e+12   
        So we need to find all client-side code that depends on this and change it. 
		So it has to be the job anyone receiving the json to convert strings to numbers as needed. 
		Also consider partners like passport360 who use the api (notify them)
		(or we can find a better json_encode where BIGINT_AS_STRING takes precedence over NUMERIC_CHECK as it "should")
*/