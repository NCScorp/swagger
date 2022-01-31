<?php

namespace Nasajon\AppBundle\Entity\Common;


class XmlConverter
{
    private $arrTagsItemLista = [];
    public function __construct() {}
    
    /**
     * Converte o array em uma string de XML
     */
    public function converterArrayToXmlString($arrDados, $config): string {
        // Seto array de nomes dos filhos de listas
        $this->arrTagsItemLista = $config['arrTagsItemLista'];

        // Crio objeto de dados a ser convertido pelo xml
        $tagPrincipal = $config['tagXML'];

        // Adiciono corpo do XML
        $stringXML = $this->arrayToXmlString($arrDados, $tagPrincipal);

        return $stringXML;
    }

    /**
     * Converte uma string XML em um XML Formatado
     */
    public function converterStringXmlToFormatedXML($stringXML) {
        $dom = new \DOMDocument;
        $dom->preserveWhiteSpace = FALSE;
        $dom->loadXML($stringXML);
        $dom->formatOutput = TRUE;

        return $dom->saveXml();
    }

    /**
     * Converte um array em uma string de XML
     */
    private function arrayToXmlString($arrDados, $tag) {
        // Abro tag do item de XML
        $stringXML = '<' . $tag . '>';

        // Verifico se é uma lista
        $eLista = $this->isList($arrDados);

        // Se for uma lista, busco o nome para as tags filhas
        if ($eLista) {
            // Defino a tag filha, com o nome do pai sem o último caractere
            $tagFilha = substr($tag, 0, -1);

            // Se houver um nome específico para a tag filha, eu substituo o nome da tag filha
            if (array_key_exists($tag, $this->arrTagsItemLista)){
                $tagFilha = $this->arrTagsItemLista[$tag];
            }
        }

        // Percorro o array de dados
        foreach ($arrDados as $key => $value) {
            // Se for lista, para cada item eu chamo função para criar XML
            if ($eLista) {
                $stringXML .= $this->arrayToXmlString($value, $tagFilha);
            } 
            // Se for um array associativo, crio as tags das proprie
            else {
                $propIsArray = is_array($value);

                // Se a propriedade for um array, chamo função de transformar array em XML
                if ($propIsArray) {
                    $stringXML .= $this->arrayToXmlString($value, $key);
                }
                // Senão, crio tag xml
                else {
                    // Abro tag do valor
                    $stringXML .= '<' . $key . '>';

                    // Adiciono valor
                    $stringXML .= $value;

                    // Fecho tag do valor
                    $stringXML .= '</' . $key . '>';
                }
            }
        }

        // Fecho tag do item de XML
        $stringXML .= '</' . $tag . '>';

        return $stringXML;
    }

    /**
     * Verifica se o array é uma lista ou array associativo de objeto
     */
    private function isList($arrDados) {
        $arrChaves = array_keys($arrDados);
        $eLista = true;

        for ($i=0; $i < count($arrChaves); $i++) { 
            if (!is_integer($arrChaves[$i])){
                $eLista = false;
                break;
            }
        }

        return $eLista;
    }
}
