<?php
namespace Cerad\Bundle\ApiV1Bundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse as BaseResponse;

/* =============================================================
 * My own json response class
 */
class JsonResponse extends BaseResponse
{
    // Make pretty for debugging 5.4 or greater
    public function setData($data = array())
    {
        // Encode <, >, ', &, and " for RFC4627-compliant JSON, which may also be embedded into HTML.
        $options = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
        
        if (defined('JSON_PRETTY_PRINT')) $options |= JSON_PRETTY_PRINT;
        
        // Angular security prefix
      //$this->data = ")]}',\n" . json_encode($data, $options);
        
        // Keep it imple for now
        $this->data = json_encode($data, $options);

        return $this->update();
    }    
}
?>
