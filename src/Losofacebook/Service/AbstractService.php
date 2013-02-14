<?php

namespace Losofacebook\Service;

use Doctrine\DBAL\Connection;
use ArrayIterator;

abstract class AbstractService
{

    /**
     * @var Connection
     */
    protected $conn;

    private $tableName;

    public function __construct(Connection $conn, $tableName)
    {
        $this->conn = $conn;
        $this->tableName = $tableName;
    }

    /**
     * @param array $params
     */
    public function findByParams(array $params = [], $options = [], callable $callback) {

        $qb = $this->conn->createQueryBuilder();
        $qb->select('*')->from($this->tableName, 'tbl');
        foreach ($params as $key => $value) {

            if (is_array($value)) {

                if (!$value) {
                    $value = [-1];
                }

                $qb->andWhere(
                    $qb->expr()->comparison($key, 'IN', '('. implode(', ', $value) . ')')
                );

            } else {
                $qb->andWhere("{$key} = " . $qb->expr()->literal($value));
            }
        }

        if (isset($options['orderBy'])) {

            $orderBy = $options['orderBy'];

            if (!is_array($orderBy)) {
                $orderBy = [$orderBy];
            }

            foreach ($orderBy as $ob) {

                $ob = explode(" ", $ob);

                if (isset($ob[1])) {
                    $qb->orderBy($ob[0], $ob[1]);
                } else {
                    $qb->orderBy($ob[0]);
                }

            }
        }

        if (isset($options['page'])) {
            $qb->setFirstResult(($options['page'] -1) * $options['limit']);
            $qb->setMaxResults($options['limit']);
        }

        $raw = array_map(
            function($data) use ($callback) {
                return $callback($data);
            },
            $this->conn->fetchAll($qb)
        );

        return new ArrayIterator($raw);

    }



}
