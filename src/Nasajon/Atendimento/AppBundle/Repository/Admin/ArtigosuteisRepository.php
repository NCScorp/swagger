<?php

namespace Nasajon\Atendimento\AppBundle\Repository\Admin;

use Nasajon\MDABundle\Repository\Atendimento\Admin\ArtigosuteisRepository as ParentRepository;

class ArtigosuteisRepository extends ParentRepository {
    public function getInfoArtigoUtil($artigo, $tenant) {
        $sql = "SELECT * FROM atendimento.artigosuteis WHERE artigo = :artigo AND tenant = :tenant";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            'artigo' => $artigo,
            'tenant' => $tenant
        ]);

        $response = $stmt->fetchAll();

        for ($i = 0; $i < count($response); $i++) {
            $response[$i]['created_by'] = json_decode($response[$i]['created_by']);
        }

        return $response;
    }
}