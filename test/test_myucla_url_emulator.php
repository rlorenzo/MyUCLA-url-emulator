<?php
/*
 * Test suite for MyUCLA url emulator. Must be called via the command line and
 * have the $test_server_url value set in config.php.
 */

require_once(dirname(__FILE__) . '/../config.php');

class UrlUpdaterTest extends PHPUnit_Framework_TestCase
{
    public function testBadTerm()
    {
        // create parameters of bad term with good srs and url
        $params = array('term' => '012',
                        'srs' => '123456789',
                        'url' => 'github.com');
        $result = $this->contact_myucla_url_updater($params);
        
        $this->assertTrue(false !== strpos($result, STATUS_INVALID_COURSE), 
                "Got response: $result");
    }

    public function testBadSrs()
    {
        // create parameters of bad term with good srs and url
        $params = array('term' => '11F',
                        'srs' => '12345678',
                        'url' => 'github.com');
        $result = $this->contact_myucla_url_updater($params);
        
        $this->assertTrue(false !== strpos($result, STATUS_INVALID_COURSE));
    }
    
    public function testEmptyParams()
    {
        $params = array();
        $result = $this->contact_myucla_url_updater($params);

        $this->assertTrue(empty($result), "Got response: $result");        
    }

    public function testGetBadCourse()
    {
        $params = array('term' => '11F',
                        'srs' => '12345678');
        $result = $this->contact_myucla_url_updater($params);

        $this->assertTrue(empty($result), "Got response: $result");        
    }    
    
    /**
     * Does basic test of updating URL.
     */
    public function testValidParams()
    {
        // set URL
        $params = array('term' => '11F',
                        'srs' => '123456789',
                        'url' => 'github.com');
        $result = $this->contact_myucla_url_updater($params);
        
        $this->assertTrue(false !== strpos($result, STATUS_SUCCESS), 
                "Got response: $result");
    }

    /**
     * Makes sure that you can query a URL
     */
    public function testSetAndGetUrl()
    {
        // set URL
        $params = array('term' => '11F',
                        'srs' => '123456789',
                        'url' => 'github.com');
        $saved_url = $params['url'];
        $result = $this->contact_myucla_url_updater($params);
        
        $this->assertTrue(false !== strpos($result, STATUS_SUCCESS), 
                "Got response: $result");
        

        unset($params['url']);  // make sure that URL was cleared
        $result = $this->contact_myucla_url_updater($params);
        
        $this->assertTrue($result == $saved_url, "Got response: $result");        
    }        
    
    /**
     * Makes sure that you can clear URL
     */
    public function testSettingAndClearingUrl()
    {
        // set URL
        $params = array('term' => '11F',
                        'srs' => '123456789',
                        'url' => 'github.com');
        $result = $this->contact_myucla_url_updater($params);
        
        $this->assertTrue(false !== strpos($result, STATUS_SUCCESS), 
                "Got response: $result");
        
        // clear URL for house keeping
        $params['url'] = '';
        $result = $this->contact_myucla_url_updater($params);
        
        $this->assertTrue(false !== strpos($result, STATUS_SUCCESS), 
                "Got response: $result");

        // make sure that URL was cleared
        unset($params['url']);
        $result = $this->contact_myucla_url_updater($params);
        
        $this->assertTrue(empty($result), "Got response: $result");        
    }    
    
    /**
     * Make sure that the URL is properly encoded and decoded, meaning that
     * get parameters are properly saved.
     */
    public function testEncodedUrl()
    {
        // set URL
        $test_url = 'test.com?param1=somewhere&param2=outthere';
        $params = array('term' => '11F',
                        'srs' => '123456789',
                        'url' => urlencode($test_url));
        $result = $this->contact_myucla_url_updater($params);
        
        $this->assertTrue(false !== strpos($result, STATUS_SUCCESS), 
                "Got response: $result");        

        unset($params['url']);  // make sure that URL was cleared
        $result = $this->contact_myucla_url_updater($params);
        
        $this->assertTrue($result == $test_url, "Got response: $result");        
    }         
    
    /** 
     * Calls MyUCLA url emulator server with given parameters.
     * 
     * @return mixed    False on error, else returns result from MyUCLA url
     *                  emulator.
     */
    private function contact_myucla_url_updater($params) {
        global $test_server_url;
        $url = '';
        
        if (empty($test_server_url)) {
            return false; // fail right away
        }
        
        // built test url assuming that key names are the GET parameter names
        $url = $test_server_url . '?';
        foreach ($params as $key => $value) {
            $url .= $key . '=' . $value . '&';
        }
                
        $response = file_get_contents($url);                
        $content = trim(strip_tags($response), " \r\n\t");

        return $content;
    }    
}
?>
