<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Utilisateur;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/index", name="home_member")
     */
    public function indexAction(Request $r)
    {
        $user = $this->getUser();
        return $this->render('AppBundle:Membre:index.html.twig',array(
            'user' => $user,
        ));
    }
    /**
     * @Route("/admin/index", name="home_admin")
     */
    public function indexAdminAction(Request $r)
    {
        $user = $this->getUser();
        return $this->render('AppBundle:Admin:index.html.twig',array(
            'user' => $user,
        ));
    }



    /**
     * @Route("/reclamation", name="reclamationName")
     */
    public function reclamationAction()
    {
        $user = $this->getUser();
        return $this->render('AppBundle:Membre:reclamation.html.twig',array(
            'user' => $user,
        ));
    }
    /**
     * @Route("/adoption", name="adoptionName")
     */
    public function adoptionPrincipaleAction()
    {
        $user = $this->getUser();
        return $this->render('AppBundle:Membre:adoption_liste.html.twig',array(
            'user' => $user,
        ));
    }
    /**
     * @Route("/veterinaires", name="veterinaireName")
     */
    public function veterinaireAction()
    {
        $user = $this->getUser();
        return $this->render('AppBundle:Membre:veterinaire.html.twig',array(
            'user' => $user,
        ));
    }
    /**
     * @Route("/accouplement", name="accouplementName")
     */
    public function accouplementAction()
    {
        $user = $this->getUser();
        return $this->render('AppBundle:Membre:accouplement_liste.html.twig',array(
            'user' => $user,
        ));
    }
    /**
     * @Route("/sitting", name="sittingName")
     */
    public function sittingAction()
    {
        $user = $this->getUser();
        return $this->render('AppBundle:Membre:sitting.html.twig',array(
            'user' => $user,
        ));
    }
    /**
     * @Route("/walking", name="walkingName")
     */
    public function walkingAction()
    {
        $user = $this->getUser();
        return $this->render('AppBundle:Membre:walking.html.twig',array(
            'user' => $user,
        ));
    }
    /**
     * @Route("/editImage", name="editImage")
     */
    public function editImageAction(Request $request)
    {
        $dir=$_SERVER['DOCUMENT_ROOT'];
        $em=$this->getDoctrine()->getManager();
        $user = new Utilisateur();
        $user=$em->getRepository("AppBundle:Utilisateur")->find($this->getUser()->getId());
        if($request->files->get('im')!=null)
        {
            if($user->getAvatar()!='user.png')
            unlink($dir."/paw_web/web/images/pawUsers/".$user->getAvatar());
            $user->setAvatar($request->files->get('im'));
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file1 */
            $file1 =  $user->getAvatar();
            $fileName1 = $this->generateUniqueFileName().'.'.$file1->guessExtension();
            $file1->move(
                $this->getParameter('user_directory'),
                $fileName1
            );
            $user->setAvatar( $fileName1);
        }

        $em->flush();
        return $this->redirectToRoute('fos_user_profile_show');
    }
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

}
