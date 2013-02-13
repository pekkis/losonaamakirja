<?php

namespace Losofacebook\Service;
use Doctrine\DBAL\Connection;
use Imagick;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Image service
 */
class ImageService
{
    const COMPRESSION_TYPE = Imagick::COMPRESSION_JPEG;

    /**
     * @var Connection
     */
    private $conn;



    /**
     * @param $basePath
     */
    public function __construct(Connection $conn, $basePath)
    {
        $this->conn = $conn;
        $this->basePath = $basePath;
    }

    /**
     * Creates image
     *
     * @param $path
     * @return integer
     */
    public function createImage($path)
    {
        $this->conn->insert(
            'image',
            [
                'upload_path' => $path
            ]
        );
        $id = $this->conn->lastInsertId();

        $img = new Imagick($path);

        $img->setImageFormat("jpeg");
        $img->setImageCompression(self::COMPRESSION_TYPE);
        $img->setImageCompressionQuality(90);
        $img->scaleImage(1200, 1200, true);
        $img->writeImage($this->basePath . '/' . $id);

        $this->createVersions($id);

        return $id;
    }

    public function createVersions($id)
    {
        $img = new Imagick($this->basePath . '/' . $id);
        $thumb = clone $img;

        $thumb->cropThumbnailimage(500, 500);
        $thumb->setImageCompression(self::COMPRESSION_TYPE);
        $thumb->setImageCompressionQuality(90);
        $thumb->writeImage($this->basePath . '/' . $id . '-thumb');
    }

    public function getImageResponse($id, $version = null)
    {
        $path = $this->basePath . '/' . $id;

        if ($version) {
            $path .= '-' . $version;
        }

        if (!is_readable($path)) {
            throw new NotFoundHttpException('Image not found');
        }

        $response = new Response();
        $response->setContent(file_get_contents($path));
        $response->headers->set('Content-type', 'image/jpeg');
        return $response;
    }


}
