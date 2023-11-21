<?php

namespace App\Controller;

use App\Entity\Schedule;
use App\Form\ScheduleType;
use App\Repository\ScheduleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/schedule')]
class ScheduleController extends AbstractController
{
    #[Route('/', name: 'app_schedule_index', methods: ['GET'])]
    public function index(ScheduleRepository $scheduleRepository,  PaginatorInterface $paginator, Request $request): Response
    {
        $schedules = $scheduleRepository->findAll();
        $fullDoctor = [];
        foreach ($schedules as $schedule) {
            $doctor = $schedule->getDoctor();
            if ($doctor !== null) {
                foreach ($doctor as $doctorItem) {
                    $fullDoctor[] = $schedule;
                }
            }

        }
        $pageSize = 10; // Número de elementos por página

        // Obtén la consulta sin ejecutarla aún
        $query = $scheduleRepository->createQueryBuilder('d')
            ->orderBy('d.id', 'ASC')
            ->getQuery();

        // Pagina los resultados utilizando el paginador
        $schedules = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $pageSize
        );
        return $this->render('schedule/index.html.twig', [
            'schedules' => $schedules,
            'fullDoctor' => $fullDoctor,
        ]);
    }

    #[Route('/new', name: 'app_schedule_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {

        $schedule = new Schedule();
        $form = $this->createForm(ScheduleType::class, $schedule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($schedule);
            $entityManager->flush();

            return $this->redirectToRoute('app_schedule_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('schedule/new.html.twig', [
            'schedule' => $schedule,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_schedule_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Schedule $schedule): Response
    {
        return $this->render('schedule/show.html.twig', [
            'schedule' => $schedule,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_schedule_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Schedule $schedule, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ScheduleType::class, $schedule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_schedule_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('schedule/edit.html.twig', [
            'schedule' => $schedule,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_schedule_delete', methods: ['POST'])]
    public function delete(Request $request, Schedule $schedule, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$schedule->getId(), $request->request->get('_token'))) {
            $entityManager->remove($schedule);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_schedule_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/list/{page}', name: 'schedule_list', methods: ['GET'])]
    public function specialityList(ScheduleRepository $scheduleRepository, PaginatorInterface $paginator, Request $request, int $page = 1): Response
    {
        $pageSize = 10;

        $query = $scheduleRepository->createQueryBuilder('s')
            ->orderBy('s.id', 'ASC')
            ->getQuery();

        $schedules = $paginator->paginate(
            $query,
            $page,
            $pageSize
        );

        return $this->render('schedule/index.html.twig', [
            'schedules' => $schedules,
        ]);
    }

}
