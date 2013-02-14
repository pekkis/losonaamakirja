<?php

namespace Losofacebook\Service;
use Doctrine\DBAL\Connection;
use Losofacebook\Company;

use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Company service
 */
class CompanyService extends AbstractService
{

    public function __construct(Connection $conn)
    {
        parent::__construct($conn, 'company');
    }

    public function findByName($name)
    {
        return $this->findBy(['name' => $name], [])->current();
    }

    public function findById($id)
    {
        return $this->findBy(['id' => $id], [])->current();
    }

    /**
     * @param array $params
     */
    public function findBy(array $params = [], $options = [])
    {
        return parent::findByParams($params, $options, function ($data) {
            return $this->createCompany($data);
        });
    }

    /**
     * @param $data
     * @return Company
     */
    protected function createCompany($data)
    {
        $obj = Company::create($data);
        return $obj;
    }
}
