<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Entity\Comment;
use App\Form\BlogType;
use App\Repository\BlogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/blog')]
final class BlogController extends AbstractController
{
    private function getSystemInfo(): array
    {
        return [
            'current_time' => new \DateTime('now', new \DateTimeZone('UTC')),
            'current_user' => $this->getUser(),
        ];
    }

    /**************************************** Admin *************************************/
    #[Route('/', name: 'app_blog_index', methods: ['GET'])]
    public function index(BlogRepository $blogRepository): Response
    {
        return $this->render('blog/index.html.twig', [
            'blogs' => $blogRepository->findAll(),
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/new', name: 'app_blog_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $blog = new Blog();
        $blog->setAuthor($this->getUser()->getUsername());
        
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($blog);
            $entityManager->flush();

            $this->addFlash('success', 'Blog created successfully!');
            
            return $this->redirectToRoute('app_blog_show', ['id' => $blog->getId()], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('blog/new.html.twig', [
            'blog' => $blog,
            'form' => $form,
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/{id}', name: 'app_blog_show', methods: ['GET'])]
    public function show(BlogRepository $blogRepository, int $id): Response
    {
        $blog = $blogRepository->find($id);
        
        if (!$blog) {
            throw $this->createNotFoundException('Blog not found');
        }

        return $this->render('blog/show.html.twig', [
            'blog' => $blog,
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/{id}/edit', name: 'app_blog_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BlogRepository $blogRepository, int $id, EntityManagerInterface $entityManager): Response
    {
        $blog = $blogRepository->find($id);
        
        if (!$blog) {
            throw $this->createNotFoundException('Blog not found');
        }

        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Blog updated successfully!');
            
            return $this->redirectToRoute('app_blog_show', ['id' => $blog->getId()], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('blog/edit.html.twig', [
            'blog' => $blog,
            'form' => $form,
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/{id}', name: 'app_blog_delete', methods: ['POST'])]
    public function delete(Request $request, BlogRepository $blogRepository, int $id, EntityManagerInterface $entityManager): Response
    {
        $blog = $blogRepository->find($id);
        
        if (!$blog) {
            throw $this->createNotFoundException('Blog not found');
        }

        if ($this->isCsrfTokenValid('delete'.$blog->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($blog);
            $entityManager->flush();
            
            $this->addFlash('success', 'Blog deleted successfully!');
        }

        return $this->redirectToRoute('app_blog_index', [], Response::HTTP_SEE_OTHER);
    }

    /**************************************** User *************************************/
    #[Route('/user', name: 'app_blog_index_user', methods: ['GET'])]
    public function indexUser(BlogRepository $blogRepository): Response
    {
        return $this->render('blog/indexUser.html.twig', [
            'blogs' => $blogRepository->findAll(),
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/user/{id}', name: 'app_blog_show_user', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function showUser(BlogRepository $blogRepository, int $id): Response
    {
        $blog = $blogRepository->find($id);
        
        if (!$blog) {
            throw $this->createNotFoundException('Blog not found');
        }

        return $this->render('blog/showUser.html.twig', [
            'blog' => $blog,
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/user/{id}/edit', name: 'app_blog_edit_user', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function editUser(Request $request, BlogRepository $blogRepository, int $id, EntityManagerInterface $entityManager): Response
    {
        $blog = $blogRepository->find($id);
        
        if (!$blog) {
            throw $this->createNotFoundException('Blog not found');
        }

        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Blog updated successfully!');
            
            return $this->redirectToRoute('app_blog_show_user', ['id' => $blog->getId()], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('blog/editUser.html.twig', [
            'blog' => $blog,
            'form' => $form,
            ...$this->getSystemInfo()
        ]);
    }
    
    #[Route('/user/new', name: 'app_blog_new_user', methods: ['GET', 'POST'])]
    public function newUser(Request $request, EntityManagerInterface $entityManager): Response
    {
        $blog = new Blog();
        $blog->setAuthor($this->getUser()->getUsername());
        
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($blog);
            $entityManager->flush();

            $this->addFlash('success', 'Blog created successfully!');
            
            return $this->redirectToRoute('app_blog_show_user', ['id' => $blog->getId()], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('blog/newUser.html.twig', [
            'blog' => $blog,
            'form' => $form,
            ...$this->getSystemInfo()
        ]);
    }

    /**************************************** Comments *************************************/
    #[Route('/{id}/comment/new', name: 'app_blog_comment_new', methods: ['POST'])]
    public function newComment(Request $request, BlogRepository $blogRepository, int $id, EntityManagerInterface $entityManager): Response
    {
        $blog = $blogRepository->find($id);
        
        if (!$blog) {
            throw $this->createNotFoundException('Blog not found');
        }

        $content = $request->request->get('content');
        $user = $this->getUser();
        
        if (!$content) {
            $this->addFlash('error', 'Comment cannot be empty');
            return $this->redirectToRoute('app_blog_show', ['id' => $blog->getId()]);
        }
        
        $comment = new Comment($content, $user->getUsername(), $user->getId(), $blog);
        
        $entityManager->persist($comment);
        $entityManager->flush();
        
        $this->addFlash('success', 'Comment added successfully!');
        
        // Redirect based on the referer
        $referer = $request->headers->get('referer');
        if ($referer && strpos($referer, '/blog/user') !== false) {
            return $this->redirectToRoute('app_blog_show_user', ['id' => $blog->getId()]);
        }
        
        return $this->redirectToRoute('app_blog_show', ['id' => $blog->getId()]);
    }

    #[Route('/comment/{id}/edit', name: 'app_comment_edit', methods: ['GET', 'POST'])]
    public function editComment(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $commentRepository = $entityManager->getRepository(Comment::class);
        $comment = $commentRepository->find($id);
        
        if (!$comment) {
            throw $this->createNotFoundException('Comment not found');
        }
        
        // Check if the current user is the author of the comment
        if ($comment->getUserId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException('You cannot edit this comment');
        }
        
        $content = $request->request->get('content');
        
        if ($request->isMethod('POST') && $content) {
            $comment->setContent($content);
            $entityManager->flush();
            
            $this->addFlash('success', 'Comment updated successfully!');
            
            $blog = $comment->getBlog();
            
            // Redirect based on the referer
            $referer = $request->headers->get('referer');
            if ($referer && strpos($referer, '/blog/user') !== false) {
                return $this->redirectToRoute('app_blog_show_user', ['id' => $blog->getId()]);
            }
            
            return $this->redirectToRoute('app_blog_show', ['id' => $blog->getId()]);
        }
        
        return $this->render('blog/editComment.html.twig', [
            'comment' => $comment,
            ...$this->getSystemInfo()
        ]);
    }

    #[Route('/comment/{id}/delete', name: 'app_comment_delete', methods: ['POST'])]
    public function deleteComment(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $commentRepository = $entityManager->getRepository(Comment::class);
        $comment = $commentRepository->find($id);
        
        if (!$comment) {
            throw $this->createNotFoundException('Comment not found');
        }
        
        // Check if the current user is the author of the comment
        if ($comment->getUserId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException('You cannot delete this comment');
        }
        
        $blog = $comment->getBlog();
        
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();
            
            $this->addFlash('success', 'Comment deleted successfully!');
        }
        
        // Redirect based on the referer
        $referer = $request->headers->get('referer');
        if ($referer && strpos($referer, '/blog/user') !== false) {
            return $this->redirectToRoute('app_blog_show_user', ['id' => $blog->getId()]);
        }
        
        return $this->redirectToRoute('app_blog_show', ['id' => $blog->getId()]);
    }
}