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
     * Uploads image
     *
     * @param $path
     */
    public function createImage($path)
    {
        $compressionType = Imagick::COMPRESSION_JPEG;

        $this->conn->insert('image', array());
        $id = $this->conn->lastInsertId();

        $img = new Imagick($path);
        $thumb = clone $img;

        $img->setImageFormat("jpeg");
        $img->setImageCompression($compressionType);
        $img->setImageCompressionQuality(90);

        $img->scaleImage(1200, 1200, true);
        $img->writeImage($this->basePath . '/' . $id);

        $thumb->cropThumbnailimage(500, 500);
        $thumb->setImageCompression($compressionType);
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
