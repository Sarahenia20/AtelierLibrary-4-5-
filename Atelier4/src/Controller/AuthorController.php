<?php

namespace App\Controller;
use App\Form\AuthorType;
use App\Entity\Author;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{
    #[Route('/author', name: 'app_author')]
    public function index(): Response
    {
        return $this->render('author/index.html.twig', [
            'controller_name' => 'AuthorController',
        ]);
    }
    #[Route('/fetch', name: 'fetch')] 
    public function fetch(AuthorRepository $repo): Response
    {
        $result = $repo->findAll();

        return $this->render('author/list.html.twig', [
            'response' => $result,
        ]);
    }
    #[Route('/add', name: 'add')] 
     public function add(ManagerRegistry $mr,AuthorRepository $repo): Response
    {
        $a=$repo->find('');
        $a= new Author();
        $a->setUsername('test');
        $a->setEmail('test@gmail.com');
        $em = $mr->getManager();
        $em->persist($a);
        $em->flush();
       return $this ->redirectToRoute('fetch');
     }
     #[Route('/addF', name: 'addF')] 
     public function addF(ManagerRegistry $mr,Request $req): Response
     {
         $a = new Author();
         $form=$this->createform(AuthorType::class, $a);
         $form->handleRequest($req);
         if($form->isSubmitted()){
             $em = $mr->getManager(); 
             $em->persist($a);
             $em->flush();
             return $this ->redirectToRoute('fetch');    
         }
         
         return $this-> render('author/formulaire.html.twig',[
             'f'=>$form->createView()
         ]);
     }
     #[Route('/update/{id}', name: 'update')]
     public function updateAuthor(int $id, ManagerRegistry $mr, Request $req, AuthorRepository $repo): Response
     {
         $a = $repo->find($id);
         if (!$a) {
             throw $this->createNotFoundException('Author not found.');
         }
     
         $form = $this->createForm(AuthorType::class, $a); 
         $form->handleRequest($req);
     
         if ($form->isSubmitted() && $form->isValid()) {
             $em = $mr->getManager();
             $em->flush();
             return $this->redirectToRoute('fetch'); 
         }
         return $this->render('author/formulaire.html.twig', [
             'f' => $form->createView()
         ]);
     }
 #[Route('/remove/{id}', name: 'remove')]
 public function remove(AuthorRepository $repo, $id, ManagerRegistry $mr):Response
 {
     $author = $repo->find($id);
     $em = $mr->getManager();
     $em->remove($author);
     $em->flush();
 
     return $this ->redirectToRoute('fetch');
 }
 #[Route('/list-author-by-email', name: 'list_author_by_email')]
 public function listAuthorByEmail(AuthorRepository $authorRepository): Response
 {
     $authors = $authorRepository->listAuthorByEmail();

     return $this->render('author/emailAuthor.twig', ['authors' => $authors]);
 }
 #[Route('/search-authors-by-book-count', name: 'search_authors_by_book_count')]
    public function searchAuthorsByBookCount(AuthorRepository $authorRepository, Request $request): Response
    {
        $minBookCount = $request->query->get('minBookCount');
        $maxBookCount = $request->query->get('maxBookCount');

        $authors = $authorRepository->findAuthorsByBookCountRange($minBookCount, $maxBookCount);

        return $this->render('author/list.html.twig', ['authors' => $authors]);
    }
    #[Route('/nobookrm', name: 'delete_authors_with_no_books')]
    public function deleteAuthorsWithNoBooks(AuthorRepository $authorRepository, EntityManagerInterface $entityManager): Response
    {
        $authorsToDelete = $authorRepository->findAuthorsWithNoBooks();

        foreach ($authorsToDelete as $author) {
            $entityManager->remove($author);
        }

        $entityManager->flush();

        return $this->redirectToRoute('/fetch');
    }
}
