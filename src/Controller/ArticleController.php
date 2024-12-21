<?php


namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Dto\ArticleFormSearch;
use App\Form\ArticleFormSearchType;
use App\Repository\ArticleRepository;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArticleController extends AbstractController
{
    




    #[Route('/article', name: 'app_article')]
    public function index(ArticleRepository $articleRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $articleFormSearch = new ArticleFormSearch();
        $searchArticleform = $this->createForm(ArticleFormSearchType::class, $articleFormSearch);
        $searchArticleform->handleRequest($request);

        $article = new Article();
        $createArticleForm = $this->createForm(ArticleType::class, $article);
        $createArticleForm->handleRequest($request);

        $page = $request->query->getInt('page', 1);
        $limit = 2;

        $articles = $articleRepository->findAllArtticles($page, $limit);
        $count = $articles->count();

        $errors = [];
        $showModal = false;

        if ($searchArticleform->isSubmitted() && $searchArticleform->isValid()) {
            $nomArticle = $articleFormSearch->getNomArticle();
            $statut = $articleFormSearch->getStatut();

            if ($nomArticle) {
                $articles = $articleRepository->findBy(['nomArticle' => $nomArticle]);
            } elseif ($statut && $statut !== "ALL") {
                $articles = $articleRepository->findByStatut($statut);
                $count = count($articles);
            } else {
                $articles = $articleRepository->findAllArtticles($page, $limit);
                $count = $articles->count();
            }
        }
        $nbrePages = ceil($count / $limit);
        if ($createArticleForm->isSubmitted()) {
            $showModal = true;
            $nomArticle = $createArticleForm->get('nomArticle')->getData();
            $existingArtice = $articleRepository->findOneBy(['nomArticle' => $nomArticle]);
            if ($existingArtice) {
                $createArticleForm->get('nomArticle')->addError(new FormError('Ce login existe déjà. Veuillez en choisir un autre.'));
                
            }
            if (count($errors) > 0 || !$createArticleForm->isValid()) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
        
                return $this->render('article/index.html.twig', [
                    'articles' => $articles,
                    'searchArticleform' => $searchArticleform->createView(),
                    'createArticleForm' => $createArticleForm->createView(),
                    'nbrePages' => $nbrePages,
                    'page' => $page,
                    'errors' => $errors,
                    'showModal' => true,
                ]);
            }


            if ($createArticleForm->isValid()) {
                $entityManager->persist($article);
                $entityManager->flush();

                $this->addFlash('success', 'Article ajouté avec succès.');
                return $this->redirectToRoute('app_article');
            } else {
                $showModal = true; 
                foreach ($createArticleForm->getErrors(true, true) as $error) {
                    $errors[] = $error->getMessage();
                }
                return $this->render('article/index.html.twig', [
                    'articles' => $articles,
                    'searchArticleform' => $searchArticleform->createView(),
                    'createArticleForm' => $createArticleForm->createView(),
                    'nbrePages' => $nbrePages,
                    'page' => $page,
                    'errors' => $errors,
                    'showModal' => $showModal,
                ]);
            }
        }

        

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'searchArticleform' => $searchArticleform->createView(),
            'createArticleForm' => $createArticleForm->createView(),
            'nbrePages' => $nbrePages,
            'page' => $page,
            'errors' => $errors,
            'showModal' => $showModal,
        ]);
    }

    

    #[Route('/article/update-quantities', name: 'update_article_quantities', methods: ['POST'])]
public function updateQuantities(Request $request, ArticleRepository $articleRepository, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    if (!is_array($data)) {
        return new JsonResponse(['error' => 'Données invalides'], 400);
    }

    foreach ($data as $item) {
        $article = $articleRepository->find($item['id']);

        if ($article) {
            $article->setQteStock(max(0, (int)$item['quantity'])); // Mise à jour de la quantité
            $entityManager->persist($article);
        }
    }

    $entityManager->flush();

    return new JsonResponse(['success' => true]);
}



    #[Route('/article/DIS', name: 'app_DIS_article')]
    public function ArticlesDIS(
        ArticleRepository $articleRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $articleFormSearch = new ArticleFormSearch();
        $searchArticleform = $this->createForm(ArticleFormSearchType::class, $articleFormSearch);
        $searchArticleform->handleRequest($request);

        $article = new Article();
        $createArticleForm = $this->createForm(ArticleType::class, $article);
        $createArticleForm->handleRequest($request);

        $page = $request->query->getInt('page', 1);
        $limit = 2;

        $articles = $articleRepository->findDIS($page, $limit);
        $count = $articles->count();

        $errors = [];
        $showModal = false;

        if ($searchArticleform->isSubmitted() && $searchArticleform->isValid()) {
            $nomArticle = $articleFormSearch->getNomArticle();
            if ($nomArticle) {
                $articles = $articleRepository->findBy(['nomArticle' => $nomArticle]);
            } else {
                $articles = $articleRepository->findDIS($page, $limit);
                $count = $articles->count();
            }
        }

        $nbrePages = ceil($count / $limit);

        if ($createArticleForm->isSubmitted()) {
            $showModal = true;

            if ($createArticleForm->isValid()) {
                $entityManager->persist($article);
                $entityManager->flush();

                $this->addFlash('success', 'Article ajouté avec succès.');
                return $this->redirectToRoute('app_DIS_article');
            } else {
                foreach ($createArticleForm->getErrors(true, true) as $error) {
                    $errors[] = $error->getMessage();
                }
            }
        }

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'searchArticleform' => $searchArticleform->createView(),
            'createArticleForm' => $createArticleForm->createView(),
            'nbrePages' => $nbrePages,
            'page' => $page,
            'errors' => $errors,
            'showModal' => $showModal,
        ]);
    }

    #[Route('/article/RUP', name: 'app_RUP_article')]
    public function ArticlesRUP(
        ArticleRepository $articleRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $articleFormSearch = new ArticleFormSearch();
        $searchArticleform = $this->createForm(ArticleFormSearchType::class, $articleFormSearch);
        $searchArticleform->handleRequest($request);

        $article = new Article();
        $createArticleForm = $this->createForm(ArticleType::class, $article);
        $createArticleForm->handleRequest($request);

        $page = $request->query->getInt('page', 1);
        $limit = 2;

        $articles = $articleRepository->findRUP($page, $limit);
        $count = $articles->count();

        $errors = [];
        $showModal = false;

        if ($searchArticleform->isSubmitted() && $searchArticleform->isValid()) {
            $nomArticle = $articleFormSearch->getNomArticle();
            if ($nomArticle) {
                $articles = $articleRepository->findBy(['nomArticle' => $nomArticle]);
            } else {
                $articles = $articleRepository->findRUP($page, $limit);
                $count = $articles->count();
            }
        }

        $nbrePages = ceil($count / $limit);

        if ($createArticleForm->isSubmitted()) {
            $showModal = true;

            if ($createArticleForm->isValid()) {
                $entityManager->persist($article);
                $entityManager->flush();

                $this->addFlash('success', 'Article ajouté avec succès.');
                return $this->redirectToRoute('app_RUP_article');
            } else {
                foreach ($createArticleForm->getErrors(true, true) as $error) {
                    $errors[] = $error->getMessage();
                }
            }
        }

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'searchArticleform' => $searchArticleform->createView(),
            'createArticleForm' => $createArticleForm->createView(),
            'nbrePages' => $nbrePages,
            'page' => $page,
            'errors' => $errors,
            'showModal' => $showModal,
        ]);
    }
    }




