<?php

namespace App\Controller;
use App\Form\BookType;
use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    #[Route('/book', name: 'app_book')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }
    #[Route('/fetch-books', name: 'fetch_books')]
    public function fetchBooks(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $bookRepository = $em->getRepository(Book::class);

        $publishedBooks = $bookRepository->findBy(['published' => true]);
        $unpublishedBooks = $bookRepository->findBy(['published' => false]);

        return $this->render('book/booklist.html.twig', [
            'publishedBooks' => $publishedBooks,
            'unpublishedBooks' => $unpublishedBooks,
        ]);
    }
    #[Route('/add-book', name: 'add_book')]
    public function addBook(ManagerRegistry $mr, Request $request): Response
    {
        $book = new Book();
        $form = $this->createForm( BookType::class, $book);

        $form->handleRequest($request);

        if($form->isSubmitted()){
            $em = $mr->getManager();
            // Obtenez l'entité Author liée à l'auteur du livre
            $author = $book->getAuthor();

            // Incrémentez l'attribut nb_books de l'entité Author
            $author->setNbBooks($author->getNbBooks() + 1);

            // Persistez le livre et l'entité Author mise à jour
            $em->persist($book);
            $em->persist($author);
            $em->flush();
        }

        return $this->render('book/formb.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/published-books', name: 'published_books')]
    public function listPublishedBooks(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Book::class);
        $publishedBooks = $repository->findBy(['published' => true]);

        return $this->render('book/list_published.html.twig', [
            'publishedBooks' => $publishedBooks,
        ]);
    }
    #[Route('/update/{id}', name: 'update')]
    public function updateAuthor(int $id, ManagerRegistry $mr, Request $req,    BookRepository $repo): Response
    {
        $b = $repo->find($id);
        if (!$b) {
            throw $this->createNotFoundException('Book not found.');
        }
    
        $form = $this->createForm(AuthorType::class, $b); 
        $form->handleRequest($req);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $mr->getManager();
            $em->flush();
            return $this->redirectToRoute('fetch-books'); 
        }
        return $this->render('author/formb.html.twig', [
            'f' => $form->createView()
        ]);
    }
    #[Route('/remove/{id}', name: 'remove')]
    public function remove(BookRepository $repo, $id, ManagerRegistry $mr):Response
    {
        $book = $repo->find($id);
        $em = $mr->getManager();
        $em->remove($book);
        $em->flush();
    
        return $this ->redirectToRoute('fetch-book');
    }
    
    #[Route('/show-book/{id}', name: 'show_book')]
    public function showBook(Book $book): Response
    {
        return $this->render('book/showb.html.twig', ['book' => $book]);
    }
    #[Route('/search-book', name: 'search_book')]
    public function searchBook(BookRepository $bookRepository, Request $request): Response
    {
        $ref = $request->query->get('ref');
        $book = $bookRepository->searchBookByRef($ref);

        return $this->render('book/list.html.twig', ['book' => $book]);
    }
    #[Route('/count-romance-books', name: 'count_romance_books')]
    public function countRomanceBooks(EntityManagerInterface $entityManager): Response
    {
        $dql = "SELECT COUNT(b) FROM App\Entity\Book b WHERE b.category = 'Romance'";
        $query = $entityManager->createQuery($dql);
        $count = $query->getSingleScalarResult();

        return $this->render('book/romance.html.twig', ['count' => $count]);
    }
    #[Route('/books-published-between-dates', name: 'books_published_between_dates')]
    public function booksPublishedBetweenDates(EntityManagerInterface $entityManager): Response
    {
        $startDate = new \DateTime('2014-01-01');
        $endDate = new \DateTime('2018-12-31');

        $dql = "SELECT b FROM App\Entity\Book b 
                WHERE b.publicationDate BETWEEN :start_date AND :end_date";
        $query = $entityManager->createQuery($dql)
            ->setParameters([
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

        $books = $query->getResult();

        return $this->render('book/dates.html.twig', ['books' => $books]);
    }
}



