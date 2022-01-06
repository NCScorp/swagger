<?php

namespace Nasajon\Atendimento\AppBundle\Service;

use Nasajon\Atendimento\AppBundle\Util\StringUtils;

class StringUtilsTest extends \Codeception\Test\Unit {

  public function geraResumo() {
        return array(
            array("Lorem ipsum dolor sit amet, consectetur adipiscing elit.", "Lorem ipsum dolor sit amet, consectetur adipiscing elit."),
            array("Lorem ipsum dolor sit <amet>, consectetur adipiscing elit.", "Lorem ipsum dolor sit , consectetur adipiscing elit."),
            array("Lorem ipsum dolor sit &lt;amet&gt;, consectetur adipiscing elit.", "Lorem ipsum dolor sit amet, consectetur adipiscing elit.")
        );
    }
    
    /**
     * @dataProvider geraResumo
     */
    public function testGeraResumo($text, $match) {
        $result = StringUtils::geraResumo($text);
        $this->assertEquals($match, $result);
    }
    
    public function generateFileDescription() {        
        return array(
            array('Teste.xml', 'Teste.xml'),
            array('Relatorio-teste-00-00-0000-xpto.csv', 'Relatorio-teste-00-00-0000-xpto.csv'),
            array('Maecenas tristique libero at tortor placerat non malesuada ipsum hendrerit Mauris egestas faucibus ligula elementum fermentum Maecenas non leo in odio ullamcorper gravida Vivamus eget augue hendrerit tempus enim ut.png', 'Maecenas tristique libero at tortor placerat non malesuada ipsum hendrerit Mauris egestas faucibus ligula elementum fermentum Maecenas non leo in odio ullamcorper gravida Vivamus eget augue hendre.png')            
        );
}
    
    /**
     * @dataProvider generateFileDescription
     */
    public function testGenerateFileDescription($str, $match) {
        $fileDescription = StringUtils::generateFileDescription($str);
        $this->assertEquals($match, $fileDescription);        
        $this->assertLessThanOrEqual(200, strlen($match));
    }

}
