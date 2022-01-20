<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MailerController extends AbstractController
{
    /**
     * @Route("/mail", name="mail")
     */
    public function sendEmail(MailerInterface $mailer, Request $request)
    {


        $emailForm= $this->createFormBuilder()
        ->add('message' , TextareaType::class,[
            'attr'=>array('rows' =>'5')
        ])
        ->add('submit',SubmitType::class,[
            'attr' => [
                'class' => 'btn btn-outline-primary float-right'
            ]
        ])
        ->getForm();
        
        $emailForm->handleRequest($request);

        if($emailForm->isSubmitted()){
            $input= $emailForm->getData();
            $text =($input['message']);
            $meal='meal1';
            
            $email= (new TemplatedEmail())
                ->from('meal1@menucard.wip')
                ->to('waiter@menucard.wip')
                ->subject('Order')
                ->text('extra fries')
                ->htmlTemplate('mailer/mail.html.twig')
                ->context([
                    'meal'=>$meal,
                    'text'=>$text
                ]);
            $mailer->send($email);
            $this->addFlash('message', 'message was sent');
            return $this->redirect($this->generateUrl('mail'));
        }
        return $this->render('mailer/index.html.twig',[
            'emailForm'=>$emailForm->createView()
        ]);
    }
}
