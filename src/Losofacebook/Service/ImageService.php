<?php

namespace Losofacebook\Service;
use Doctrine\DBAL\Connection;
use Imagick;
use ImagickPixel;
use Symfony\Component\HttpFoundation\Response;
use Losofacebook\Image;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Memcached;
use DateTime;

/**
 * Image service
 */
class ImageService extends AbstractService
{
    const COMPRESSION_TYPE = Imagick::COMPRESSION_JPEG;

    /**
     * @param $basePath
     */
    public function __construct(Connection $conn, $basePath, Memcached $memcached)
    {
        parent::__construct($conn, 'image', $memcached);
        $this->basePath = $basePath;
    }

    /**
     * Creates image
     *
     * @param string $path
     * @param int $type
     * @return integer
     */
    public function createImage($path, $type)
    {
        $this->conn->insert(
            'image',
            [
                'upload_path' => $path,
                'type' => $type
            ]
        );
        $id = $this->conn->lastInsertId();

        $img = new Imagick($path);
        $img->setbackgroundcolor(new ImagickPixel('white'));
        $img = $img->flattenImages();

        $img->setImageFormat("jpeg");

        $img->setImageCompression(self::COMPRESSION_TYPE);
        $img->setImageCompressionQuality(90);
        $img->scaleImage(1200, 1200, true);
        $img->writeImage($this->basePath . '/' . $id);

        if ($type == Image::TYPE_PERSON) {
            $this->createVersions($id);
        } else {
            $this->createCorporateVersions($id);
        }
        return $id;
    }


    public function createCorporateVersions($id)
    {
        $img = new Imagick($this->basePath . '/' . $id);
        $img->thumbnailimage(450, 450, true);
                
        $geo = $img->getImageGeometry();

        $x = (500 - $geo['width']) / 2;
        $y = (500 - $geo['height']) / 2;

        $image = new Imagick();
        $image->newImage(500, 500, new ImagickPixel('white'));
        $image->setImageFormat('jpeg');
        $image->compositeImage($img, $img->getImageCompose(), $x, $y);

        foreach ($this->getCorporateImageVersions() as $key => $data) {
            
            $versionPath = $this->basePath . '/' . $id . '-' . $key;
            
            $v = clone $image;
            
            list($size, $cq) = $data;
            $v->cropThumbnailimage($size, $size);
            $v->setImageCompression(self::COMPRESSION_TYPE);
            $v->setImageCompressionQuality($cq);
            $v->writeImage($this->basePath . '/' . $id . '-' . $key);
            
            $linkPath = realpath($this->basePath . '/../../../web/images')
                    . '/' . $id . '-' . $key . '.jpg';
                        
            if (!is_link($linkPath)) {
                symlink($versionPath, $linkPath);
            }            
            
        }
        
    }

    
    protected function getCorporateImageVersions()
    {
        return [
            'main' => [
                126,
                75
            ],
            'loso' => [
                306,
                75
            ]
        ];
    }

    
    
    protected function getImageVersions()
    {
        return [
            'main' => [
                126,
                75
            ],
            'mini' => [
                50,
                45
            ],
            'midi' => [
                75,
                60
            ],
            'loso' => [
                210,
                75
            ]


        ];
    }

    public function createVersions($id)
    {
        
        $img = new Imagick($this->basePath . '/' . $id);
        
        foreach ($this->getImageVersions() as $key => $data) {
            
            list($size, $cq) = $data;
            
            $versionPath = $this->basePath . '/' . $id . '-' . $key;
            
            $v = clone $img;
            $v->stripImage();
            $v->cropThumbnailimage($size, $size);
            $v->setImageCompression(self::COMPRESSION_TYPE);
            $v->setInterlaceScheme(Imagick::INTERLACE_PLANE);
            $v->setImageCompressionQuality($cq);
            $v->writeImage($versionPath);
            
            $linkPath = realpath($this->basePath . '/../../../web/images')
                    . '/' . $id . '-' . $key . '.jpg';
                        
            if (!is_link($linkPath)) {
                symlink($versionPath, $linkPath);
            }            
            
        }
       
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

        $content = file_get_contents($path);
        
        $response = new Response();
        $response->setContent($content);
        $response->headers->set('Content-type', 'image/jpeg');
    
        /*
        $now = new DateTime();
        $now->modify('+30 days');
    
        $response->setPublic(true);
        $response->setExpires($now);
        $response->setEtag(md5($content));
        */
        
        
        return $response;
    }


}
