<?php

namespace Lunar\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class CaptchaImageController
    extends AbstractActionController
{
    /**
     * Streams the requested captcha image.
     * @return Zend\Stdlib\ResponseInterface
     */
    public function captchaAction ()
    {
        $response = $this->getResponse ();
        $response->getHeaders ()->addHeaderLine (
            'Content-Type', 'image/png'
        );

        $id = $this->params ('id', false);
        if ($id) {
            $serviceLocator = $this->getServiceLocator ();
            $config = $serviceLocator->get ('Configuration');
            if (array_key_exists ('captcha_image', $config)) {
                $config = $config ['captcha_image'];
            }
            else {
                $config = array ();
            }

            $path = $this->getCaptchaPath ($config) . '/' . $id;

            if (file_exists ($path)) {
                $image = @file_get_contents ($path);

                $response->setStatusCode (200);
                $response->setContent ($image);

                if (!@unlink ($path)) {
                    if ($serviceLocator->has ('ApplicationLog')) {
                        $serviceLocator->get ('ApplicationLog')
                            ->warn ('Cannot unlink the captcha image \'' . $path . '\'.');
                    }
                }
            }
        }

        return $response;
    }

    /**
     * Returns the path to the directory containing the captcha images.
     * @param array $config
     * @return string
     */
    protected function getCaptchaPath ($config)
    {
        $imgDir = './data/captcha';
        if (array_key_exists ('imgDir', $config) && !empty ($config ['imgDir'])) {
            $imgDir = trim ($config ['imgDir']);
        }

        return $imgDir;
    }
}
