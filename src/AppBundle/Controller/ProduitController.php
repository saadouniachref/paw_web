<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Produit;
use AppBundle\Form\ProduitType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProduitController extends Controller
{

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/boutique?type={type}",name="boutique_membre")
     */
    public function boutiqueAction($type,Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($type == 'tous')
            $articles = $em->getRepository("AppBundle:Produit")->findAll();
        else
            $articles = $em->getRepository("AppBundle:Produit")->findBy(array('type' => $type));
        $paginator = $this->get('knp_paginator');
        $result= $paginator->paginate(
          $articles,
          $request->query->getInt('page',1),
          $request->query->getInt('limit',6)

        );
        return $this->renderBoutique(["user" => $user, "articles" => $result]);
    }
    /**
     * @Route("/admin/ajouterProduit" ,name="ajouterProduit")
     */
    public function gererProduitAction(Request $request) {
        $user = $this->getUser();
        $produit = new Produit();
        $em = $this->getDoctrine()->getManager();
        $Form=$this->CreateForm(ProduitType::class,$produit);
        $Form->handleRequest($request);
        if($Form->isSubmitted() && $Form->isValid())
        {
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file1 */
            $file1 = $produit->getImage1();
            $fileName1 = $this->generateUniqueFileName().'.'.$file1->guessExtension();
            $file1->move(
                $this->getParameter('produit_directory'),
                $fileName1
            );
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file2 */
            $file2 = $produit->getImage2();
            $fileName2 = $this->generateUniqueFileName().'.'.$file2->guessExtension();
            $file2->move(
                $this->getParameter('produit_directory'),
                $fileName2
            );
            $produit->setImage1($fileName1);
            $produit->setImage2($fileName2);
            $em->persist($produit);
            $em->flush();
            return $this->redirectToRoute('afficheProduit');
        }

        return $this->render('AppBundle:admin:ajouterProduit.html.twig',array(
            'user' => $user,
            'form'=>$Form->createView()
        ));
    }

    public function uploadImage($file)
    {
        /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file1 */
        $file1 = $file;
        $fileName1 = $this->generateUniqueFileName().'.'.$file1->guessExtension();
        $file1->move(
            $this->getParameter('produit_directory'),
            $fileName1
        );
        return $fileName1 ;
    }


    /**
     * @Route("/admin/afficheProduit" ,name="afficheProduit")
     */
    public function AfficheAction(Request $request)
    {
        $user = $this->getUser();
        $em=$this->getDoctrine()->getManager();
        $produits=$em->getRepository("AppBundle:Produit")->findAll();
        return $this->render('AppBundle:admin:afficheProduit.html.twig', array(
            'produits'=>$produits,
            'user' => $user,
        ));
    }


    public function renderBoutique($params)
    {
        return $this->render("AppBundle:Membre:boutique.html.twig", $params);
    }
    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }




    /**
     * @Route("/admin/supprimerProduit" ,name="supprimerProduit")
     */
    public function DeleteAction(Request $request)
    {
        $id_article = $request->get("keyword");
        $em=$this->getDoctrine()->getManager();
        $produit=$em->getRepository("AppBundle:Produit")->find($id_article);
            $produit->setQuantite(0);
            $em->flush();
            return $this->redirectToRoute('afficheProduit');

    }
    /**
     * @Route("/admin/modifierProduit/{id}" ,name="modifierProduit")
     */
    public function UpdateAction($id,Request $request)
    {
        $user = $this->getUser();
        $em=$this->getDoctrine()->getManager();
        $produit=$em->getRepository("AppBundle:Produit")->find($id);
        return $this->render('AppBundle:admin:modifierProduit.html.twig', array(
            'produit'=>$produit,
            'user'=>$user
        ));
    }


    /**
     * @Route("/admin/modifierProduit" ,name="modifierP")
     */
    public function ModifierAction(Request $request)
    {
        $dir=$_SERVER['DOCUMENT_ROOT'];
        $produit = new Produit();
        $produit->setId($request->get('id'));
        $em=$this->getDoctrine()->getManager();
        $produit=$em->getRepository("AppBundle:Produit")->find($produit->getId());
        $produit->setLibelle($request->get('libelle'));
        $produit->setType($request->get('type'));
        $produit->setDescription($request->get('description'));
        $produit->setQuantite($request->get('quantite'));
        $produit->setPrix($request->get('prix'));
        if($request->files->get('image1')!=null)
        {

            unlink($dir."/paw_web/web/images/pawBoutique/".$produit->getImage1());
            $produit->setImage1($request->files->get('image1'));
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file1 */
            $file1 =  $produit->getImage1();
            $fileName1 = $this->generateUniqueFileName().'.'.$file1->guessExtension();
            $file1->move(
                $this->getParameter('produit_directory'),
                $fileName1
            );
            $produit->setImage1( $fileName1);
        }

        if($request->files->get('image2')!=null)
        {
            unlink($dir."/paw_web/web/images/pawBoutique/".$produit->getImage2());
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file2 */
            $file2 = $request->files->get('image2');
            $fileName2 = $this->generateUniqueFileName().'.'.$file2->guessExtension();
            $file2->move(
                $this->getParameter('produit_directory'),
                $fileName2
            );
            $produit->setImage2( $fileName2);
        }

        $em->flush();
        return $this->redirectToRoute('afficheProduit');
    }
    /**
     * @Route("/detailProduit?id={id}" ,name="detailProduit")
     */
    public function DetailAction($id,Request $request)
    {
        $user = $this->getUser();
        $em=$this->getDoctrine()->getManager();
        $produit=$em->getRepository("AppBundle:Produit")->find($id);
        return $this->render('AppBundle:Membre:detailProduit.html.twig', array(
            'produit'=>$produit,
            'user'=>$user
        ));
    }

    /**
     * @Route("/membre/boutique/produit/recherche", name="rechercheProduit")
     */
    public function rechercheAction(Request $request)
    {
        $keyword = $request->get("keyword");
        $em = $this->getDoctrine()->getManager();
        $articles = $em->getRepository("AppBundle:Produit")->searchByKeyword($keyword);

        return $this->render("AppBundle:Membre:boutique.html.twig", array(
            "articles" => $articles,
            "user" => $this->getUser(),
        ));
    }
}
