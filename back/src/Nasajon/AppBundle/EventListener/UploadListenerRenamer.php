<?php

namespace Nasajon\AppBundle\EventListener;

use Oneup\UploaderBundle\Uploader\File\FileInterface;
use Oneup\UploaderBundle\Uploader\Naming\NamerInterface;

/**
 * Classe responsável por renomear os arquivos antes de ser feito o upload
 * 
 * Espera que o nome do arquivo chegue no formato
 * <tenant>:<negocio>:documentos:<arquivo.ext>
 * 
 * e então cria o diretório a partir desse nome.
 * 
 * Diretório final do S3: * /crmweb/arquivos/<tenant>/<negocio>/documentos/<arquivo.ext>
 */
class UploadListenerRenamer implements NamerInterface { 

	public function name(FileInterface $file)
	{
		$original = $file->getClientOriginalName();

		$dir = explode(':', $original);

		return implode('/', $dir);		
	}  
}