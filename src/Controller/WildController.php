<?php

// src/Controller/WildController.php
namespace App\Controller;

use App\Entity\Category;
use App\Entity\Episode;
use App\Entity\Program;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wild", name="wild_")
 */
class WildController extends AbstractController
{
    /**
     * Show all rows from Program’s entity
     *
     * @Route("/", name="index")
     * @return Response A response instance
     */
    public function index() :Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();
        if (!$programs) {
            throw $this->createAccessDeniedException(
                'No program found in program\'s table.'
            );
        }

        return $this->render('index.html.twig', [
            'website' => 'Wild Séries',
        ]);
    }

    /**
     * @Route("/show/{slug}", requirements={"slug"="[0-9a-z-]+"},
     * methods={"GET"}, defaults={"slug": "Aucune série sélectionnée, veuillez choisir une série"}, name="show")
     * @param $slug
     * @return Response
     */
    public function show(?string $slug) :Response
    {
        if (!$slug) {
            throw  $this
                ->createNotFoundException('No slug has been sent to find a program in program\'s table.');
        }

        $slug = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($slug)), "-")
        );

        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(['title' => mb_strtolower($slug)]);

        if (!$program) {
            throw $this->createNotFoundException('No program with '.$slug. 'title, found in program\'s table.');
        }

        return $this->render('wild/show.html.twig', [
            'program' => $program,
            'slug'  => $slug,
        ]);

    }

    /**
    * @param string|null $categoryName
    * @return Response
    * @Route("/category/{categoryName}", name="show_category")
    */
    public function showByCategory(string $categoryName) :Response
    {

        if (!$categoryName) {
            throw $this
                ->createNotFoundException('No categoryName has been sent to find a category in categorie\'s table.');
        }
        $categoryName = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($categoryName)), "-")
        );
        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findOneBy(['name' => mb_strtolower($categoryName)]);

        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findBy(['category' => $category], ['id' => 'DESC'], 3);

        if (!$programs) {
            throw $this->createNotFoundException(
                'No program with ' . $categoryName . ' category, found in Program\'s table.'
            );
        }

        return $this->render('wild/category.html.twig', [
            'programs' => $programs,
            'category' => $category,
        ]);
    }
    /**
     * @param int $id
     * @return Response
     * @Route("/")
     */
    public function showBySeason (int $id): Response
    {
        $program = $this->getDoctrine()
        ->getRepository(Program::class)
        ->find($id);


    }

    /**
     * @param Episode $episode
     * @return Response
     * @Route("/episode/{id}", name="show_episode")
     */
    public function showEpisode(Episode $episode):Response
    {
        $season = $episode->getSeason();
        $program = $season->getProgram();
        return $this->render('wild/episode.html.twig', [
            'episode' => $episode,
            'program' => $program,
            'season' => $season,
        ]);
    }




}