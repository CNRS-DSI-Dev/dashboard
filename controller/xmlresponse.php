<?php
/**
 * @author Patrick Paysant <patrick.paysant@linagora.com>
 * @copyright 2014 CNRS DSI
 */

namespace OCA\Dashboard\Controller;

use \OCP\AppFramework\Http\Response;

/**
 * A renderer for XML calls
 */
class XMLResponse extends \OCP\AppFramework\Http\Response {

    /**
     * response data
     * @var array|object
     */
    protected $data;


    /**
     * constructor of XMLResponse
     * @param array|object $data the object or array that should be transformed
     * @param int $statusCode the Http status code, defaults to 200
     */
    public function __construct($data=array(), $statusCode=\OCP\AppFramework\Http::STATUS_OK) {
        $this->data = $data;
        $this->setStatus($statusCode);
        $this->addHeader('Content-type', 'application/xml; charset=utf-8');
    }


    /**
     * Returns the rendered XML
     * @return string the rendered XML
     */
    public function render(){
        return $this->xml_encode($this->data);
    }

    /**
     * Sets values in the data json array
     * @param array|object $data an array or object which will be transformed
     *                             to XML
     * @return XMLResponse Reference to this object
     */
    public function setData($data){
        $this->data = $data;

        return $this;
    }


    /**
     * Used to get the set parameters
     * @return array the data
     */
    public function getData(){
        return $this->data;
    }

    /**
     * Return XML encoded array
     * @param array|object $data an array or object which will be transformed to XML
     * @return string XML string
     */
    protected function xml_encode($data) {
        $orig = array($data);
        $xml = new \SimpleXMLElement("<?xml version =\"1.0\"?><stats></stats>");
        $this->arrayToXML($orig, $xml);

        return $xml->asXML();
    }

    /**
     * Encode an array to SimpleXML object
     * @param array $data An wrapping array around your data array ($data = array($yourData))
     * @param string $xml A pre-initialized XML string : $xml = new SimpleXMLElement("<?xml version =\"1.0\"?><root></root>");
     */
    protected function arrayToXML($data, &$xml) {
        foreach($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $subnode = $xml->addChild('item' . $key);
                }
                else {
                    $subnode = $xml->addChild("$key");
                }
                $this->arrayToXML($value, $subnode);
            }
            else {
                $xml->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

}
