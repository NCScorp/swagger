<?php

namespace AppBundle\Util;

use Symfony\Component\Filesystem\Filesystem;
use ZipArchive;

class ManipuladorZipUtil {

    /**
     * 
     * @param string $nome do zip a ser criado
     * @param type $extensao do(s) arquivo(s) interno(s) ao zip, por exemplo XML
     * @param array $arquivos Array de String ou String a ser adicionado ao conteudo zippado
     * @param String local temporario para guardar o zip
     * @return Array com conteudo zipado e tamanho do zip
     */
    public static function compactar($nome, array $arquivos, $local, $keepAlive = false) {
        $fs = new Filesystem();
        $zip = new ZipArchive();
        $nome = $nome . '.zip';
        $path = $local;

        $fs->mkdir($path, 0755);

        if ($zip->open($path . $nome, ZipArchive::CREATE) !== TRUE) {
            exit("cannot open <$nome>\n");
        }

        if (is_array($arquivos)) {
            foreach ($arquivos as $k => $arquivo) {
                $zipou = $zip->addFile($arquivo, basename($arquivo));
                if (!$zipou) {
                    throw new \Exception('Falha ao zipar arquivo:' . basename($arquivo));
                }
            }
        } else {
            $zip->addFile($arquivos);
        }

        $zip->close();

        $result['arquivo'] = file_get_contents($path . $nome);
        $result['tamanho'] = filesize($path . $nome);
        $result['nome'] = $nome;
        $result['path'] = $path . $nome;

        if (!$keepAlive) {
            ManipuladorZipUtil::remover($path . $nome);
        }

        return $result;
    }

    /**
     * 
     * @param type $arquivo com caminho completo
     */
    public static function remover($arquivos) {
        $fs = new Filesystem();
        $fs->remove($arquivos);
    }

    /**
     * Cria um arquivo
     * @param type $nome
     * @param type $extensao
     * @param type $conteudo
     * @param type $path
     * @return type
     */
    public static function criar($nome, $extensao, $conteudo, $path) {
        $fs = new Filesystem();
        $fs->mkdir($path, 0755);
        $arquivo = fopen($path . $nome . '.' . $extensao, "w");
        fwrite($arquivo, $conteudo);
        fclose($arquivo);
        return $path . $nome . '.' . $extensao;
    }

    /**
     * Converte de bytes para kilobytes (binário). Utilizado para converter o tamanho do XML das notas e eventos
     * @param float $bytes
     * @return fload
     */
    public static function bytes2kilobytes(float $bytes) {
        return $bytes / 1024;
    }
}
