<?php

/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Dashboard\Controller;

use \OCP\AppFramework\Http\Response;

/**
 * A renderer for XML calls
 */
class XMLResponse extends \OCP\AppFramework\Http\Response {

    /**
     * Default root tag name
     */
    const defaultRootTagName = 'root';

    /**
     * Response datas
     * @var array|object
     */
    protected $data;

    /**
     * XML root tag name
     * @var string
     */
    protected $rootTagName;


    /**
     * constructor of XMLResponse
     * @param array|object $data the object or array that should be transformed
     * @param int $statusCode the Http status code, defaults to 200
     */
    public function __construct($data=array(), $statusCode=\OCP\AppFramework\Http::STATUS_OK, $rootTagName=self::defaultRootTagName) {
        $this->data = $data;
        $this->setStatus($statusCode);

        // TODO : this does not work... have to investigate that, later :/
        // Ref setRootTagName and xml_encode
        $this->rootTagName = $this->setRootTagName($rootTagName);

        $this->addHeader('Content-type', 'application/xml; charset=utf-8');
    }

    /**
     * Setter for rootTagName
     */
    protected function setRootTagName($rootTagName) {
        if ($this->isValidTagName($rootTagName)) {
            $this->rootTagName = $rootTagName;
        }
        else {
            $this->rootTagName = self::defaultRootTagName;
        }
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

        // TODO, ref : setRootTagName and __construct
        // $beginningTag = "<" . $this->rootTagName . ">";
        // $endingTag = "</" . $this->rootTagName . ">";

        /* $xml = new \SimpleXMLElement("<?xml version =\"1.0\"?>" . $beginningTag . $endingTag); */

        $xml = new \SimpleXMLElement("<?xml version =\"1.0\"?><root></root>");
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
                if (is_numeric($key)) {
                    $xml->addChild("item$key", htmlspecialchars("$value"));
                }
                else {
                    $xml->addChild("$key", htmlspecialchars("$value"));
                }
            }
        }
    }

    /**
     * Some verifications on tag names
     */
    protected function isValidTagName($tag){
        $pattern = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i';
        return preg_match($pattern, $tag, $matches) && $matches[0] == $tag;
    }

}
