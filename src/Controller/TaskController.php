<?php
// src/Controller/TaskController.php
namespace App\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/tasks", name="api_tasks_")
 */
class TaskController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function index(): JsonResponse
    {
        $tasks = $this->entityManager->getRepository(Task::class)->findBy([], ['completed' => 'ASC', 'deadline' => 'ASC']);
        return $this->json($tasks);
    }

    /**
     * @Route("/{id}", methods={"GET"})
     */
    public function show(int $id): JsonResponse
    {
        $task = $this->entityManager->getRepository(Task::class)->find($id);
        return $task ? $this->json($task) : $this->json(['message' => 'Task not found'], 404);
    }

    /**
     * @Route("", methods={"POST"})
     */
    public function store(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $task = new Task();
        $task->setTitle($data['title']);
        $task->setDescription($data['description'] ?? '');
        $task->setDeadline(new \DateTime($data['deadline']));
        $task->setCreatedAt(new \DateTime());
        $task->setUpdatedAt(new \DateTime());

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $this->json($task, 201);
    }

    /**
     * @Route("/{id}", methods={"PUT"})
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $task = $this->entityManager->getRepository(Task::class)->find($id);
        if (!$task) {
            return $this->json(['message' => 'Task not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $task->setTitle($data['title']);
        $task->setDescription($data['description']);
        $task->setDeadline(new \DateTime($data['deadline']));
        $task->setCompleted($data['completed']);
        $task->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return $this->json($task);
    }

    /**
     * @Route("/{id}", methods={"DELETE"})
     */
    public function destroy(int $id): JsonResponse
    {
        $task = $this->entityManager->getRepository(Task::class)->find($id);
        if (!$task) {
            return $this->json(['message' => 'Task not found'], 404);
        }

        $this->entityManager->remove($task);
        $this->entityManager->flush();

        return $this->json(['message' => 'Task deleted successfully']);
    }
}
