<?php

namespace Losofacebook\Service;
use Doctrine\DBAL\Connection;
use Losofacebook\Post;
use Losofacebook\Comment;
use Losofacebook\Service\PersonService;
use DateTime;
use Memcached;

/**
 * Image service
 */
class PostService extends AbstractService
{
    /**
     * @var PersonService
     */
    private $personService;

    /**
     * @param $basePath
     */
    public function __construct(
        Connection $conn,
        PersonService $personService,
        Memcached $memcached
    ) {
        parent::__construct($conn, 'post', $memcached);
        $this->personService = $personService;
    }

    /**
     * @param int $personId
     * @param \stdClass $data
     * @return Post
     */
    public function create($personId, $data)
    {
        $cacheId = "post_person_{$personId}";
        $this->memcached->delete($cacheId);
        
        $data = [
            'person_id' => $personId,
            'poster_id' => $data->poster->id,
            'date_created' => (new DateTime())->format('Y-m-d H:i:s'),
            'content' => $data->content,
        ];

        $this->conn->insert('post', $data);
        $data['id'] = $this->conn->lastInsertId();

        $post = Post::create($data);
        $post->setPerson($this->personService->findById($data['poster_id'], false));
        return $post;
    }

    /**
     * @param int $postId
     * @param \stdClass $data
     * @return Comment
     */
    public function createComment($postId, $data)
    {
        try {

             $post = $this->findByParams(
                 [
                     'id' => $postId
                 ],
                 [],
                 function ($data) {
                    return Post::create($data);
                 }
            )->current();

            if (!$post) {
                throw new \IllegalArgumentException("Invalid post");
            }
            
            $cacheId = "post_person_{$post->getPersonId()}";
            $this->memcached->delete($cacheId);            
            
            $data = [
                'post_id' => $postId,
                'poster_id' => $data->poster->id,
                'date_created' => (new DateTime())->format('Y-m-d H:i:s'),
                'content' => $data->content,
            ];
            $this->conn->insert('comment', $data);

            $data['id'] = $this->conn->lastInsertId();

            $comment = Comment::create($data);
            $comment->setPoster($this->personService->findById($data['poster_id'], false));
            return $comment;

        } catch (\Exception $e) {
            echo $e;
            die();
        }

    }


    /**
     * Finds by person id
     *
     * @param $path
     */
    public function findByPersonId($personId)
    {
        return $this->tryCache(
            "post_person_{$personId}",
            function () use ($personId) {

                $data = $this->conn->fetchAll(
                    "SELECT * FROM post WHERE person_id = ? ORDER BY date_created DESC", [$personId]
                );

                $posts = [];
                foreach ($data as $row) {

                    $post = Post::create($row);
                    $post->setPerson($this->personService->findById($row['poster_id'], false));
                    $post->setComments($this->getComments($row['id']));

                    $posts[] = $post;
                }

                return $posts;
                  
            },
            null
        );
        
        
    }

    public function getComments($postId)
    {
        $data = $this->conn->fetchAll(
            "SELECT * FROM comment WHERE post_id = ? ORDER BY date_created DESC", [$postId]
        );

        $comments = [];
        foreach ($data as $row) {
            $comment = Comment::create($row);
            $comment->setPoster($this->personService->findById($row['poster_id'], false));
            $comments[] = $comment;
        }
        return $comments;
    }
}
