<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 11/10/17
 * Time: 16:07
 * PHP version 7
 */

namespace Controller;

/**
 * Class HomeController
 *
 */

use Filter\Text;
use \Swift_SmtpTransport;
use \Swift_Mailer;
use \Swift_Message;

class HomeController extends AbstractController
{
    /**
     * @param array $userData
     * @return array
     */
    private function verifMail(array $userData): array
    {
        $errorsForm = [];
        if (empty($userData['lastname'])) {
            $errorsForm['lastname0'] = "Votre nom doit être indiqué";
        } elseif (!preg_match("#[a-zA-ZÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ' ]$#", $userData['lastname'])) {
            $errorsForm['invalid lastname'] = "Votre nom ne doit pas contenir de caractères spéciaux";
        }
        if (empty($userData['firstname'])) {
            $errorsForm['firstname0'] = "Votre prénom doit être indiqué";
        } elseif (!preg_match("#[a-zA-ZÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ' ]$#", $userData['firstname'])) {
            $errorsForm['invalid firstname'] = "Votre prénom ne doit pas contenir de caractères spéciaux";
        }
        if (empty($userData['email'])) {
            $errorsForm['email0'] = "Votre mail doit être indiqué";
        } elseif (!preg_match(" /^.+@.+\.[a-zA-Z]{2,}$/ ", $userData['email'])) {
            $errorsForm['invalid email'] = "Le format de l'email n'est pas correct";
        }
        if (empty($userData['num'])) {
            $errorsForm['num0'] = "Votre numéro de téléphone doit être indiqué";
        } elseif (!preg_match(" #^[0-9]{2}[-/ ]?[0-9]{2}[-/ ]?[0-9]{2}[-/ ]?[0-9]{2}[-/ ]?[0-9]{2}?$# ", $userData['num'])) {
            $errorsForm['invalid phone'] = "Le numéro de téléphone renseigné est incorrect";
        }
        if (empty($userData['message'])) {
            $errorsForm['message0'] = "Vous devez écrire un message";
        } elseif (!preg_match(" #[a-zA-ZÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ' ]$# ", $userData['message'])) {
            $errorsForm['invalid message'] = "Votre message ne doit pas contenir de caractère non-autorisés";
        }


        return $errorsForm;
    }


    /**
     * @param array $userData
     * @return string
     */
    private function sendMail(array $userData): string
    {
        $transport = (new Swift_SmtpTransport('smtp.gmail.com', 465))
            ->setUsername(APP_MAIL_USERNAME)
            ->setPassword(APP_MAIL_PASSWORD)
            ->setEncryption(APP_MAIL_ENCRYPTION);
        $mailer = new Swift_Mailer($transport);
        $message = new Swift_Message();
        $message->setSubject('Message formulaire aslo45');
        $message->setFrom([$userData['email'] => $userData['lastname'] . ' ' . $userData['firstname']]);
        $message->addTo(APP_MAIL_ADDTO , 'recipient name');
        $message->addReplyTo($userData['email'], $userData['email']);
        $message->setBody($userData['message']);
        $result = $mailer->send($message);
        return $result;

    }


    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index()
    {
        $errors = $userData = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $userData = $_POST;
            $textFilter = new Text();
            $textFilter->setTexts($userData);
            $userData = $textFilter->filter();
            $errors = $this->verifMail($userData);
            if (empty($errors)) {
                $this->sendMail($userData);
                header('location:/');
                exit();
            }
        }

        return $this->twig->render('Home/index.html.twig', ['errors' => $errors, 'post' => $userData]);

    }
}
