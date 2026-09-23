<?php

namespace App\Controller\CompanyPost;

use App\Entity\User;
use App\Entity\CompanyPost;
use App\Repository\CompanyRepository;
use App\Service\CompanyPost\CompanyPostResponseBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateCompanyPost extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, CompanyRepository $companyRepo, ?CompanyPostResponseBuilder $responseBuilder = null)
    {
        $responseBuilder ??= new CompanyPostResponseBuilder();

        $companyPost = new CompanyPost();
        $actor = $this->getUser();

        if (!$actor instanceof User) {
            return $this->json(['message' => 'Login required'], 401);
        }

        if (!$this->isGranted('ROLE_COMPANY')) {
            return $this->json(['message' => 'Only company users can create company posts'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data && $request->request->all()) {
            $data = $request->request->all();
        }

        if (!is_array($data)) {
            return $this->json(['message' => 'Invalid company post payload'], 400);
        }

        $resolveEntityId = static function ($value): ?int {
            if ($value === null || $value === '' || $value === []) {
                return null;
            }

            if (is_array($value) && isset($value['id'])) {
                $value = $value['id'];
            }

            if (is_string($value) && preg_match('/\/(\d+)$/', $value, $matches)) {
                return (int) $matches[1];
            }

            if (is_scalar($value)) {
                $raw = trim((string) $value);
                if ($raw === '' || $raw === 'null' || $raw === 'undefined') {
                    return null;
                }

                $id = (int) $raw;
                return $id > 0 ? $id : null;
            }

            return null;
        };

        if ($request->isMethod('POST')) {
            $actorCompany = $companyRepo->findOneByUser($actor);
            if ($actorCompany === null) {
                return $this->json(['message' => 'Authenticated company user is not linked to a company profile'], 422);
            }

            $payloadCompanyId = $resolveEntityId($data['companyId'] ?? $data['company'] ?? $data['company_profile'] ?? null);
            if ($payloadCompanyId !== null && $payloadCompanyId !== $actorCompany->getId()) {
                return $this->json(['message' => 'Company relation must match the authenticated company profile'], 403);
            }

            $payloadAuthorId = $resolveEntityId($data['authorId'] ?? $data['author'] ?? null);
            if ($payloadAuthorId !== null && $payloadAuthorId !== $actor->getId()) {
                return $this->json(['message' => 'Author relation must match the authenticated user'], 403);
            }

            $companyPost->setCompanyId($actorCompany);
            $companyPost->setAuthorId($actor);

            $companyPost->setTitle($data['title'] ?? null);
            $companyPost->setContent($data['content'] ?? null);
            $companyPost->setCategory($data['category'] ?? null);
            $companyPost->setVisibility($data['visibility'] ?? null);
            $companyPost->setImageUrl($this->sanitizeImageInput($data['imageUrl'] ?? $data['image'] ?? null));
            $companyPost->setPubliedAt(new \DateTimeImmutable($data['publiedAt'] ?? 'now'));
            $companyPost->setCreatedAt(new \DateTimeImmutable($data['createdAt'] ?? 'now'));
            $companyPost->setUpdatedAt(new \DateTimeImmutable($data['updatedAt'] ?? 'now'));

            $em->persist($companyPost);
            $em->flush();

            return $this->json($responseBuilder->buildItem($companyPost), 201);
        }

        return $this->json(['message' => 'Invalid request method'], 400);
    }

    private function sanitizeImageInput(mixed $image): ?string
    {
        if (!is_string($image)) {
            return null;
        }

        $normalized = trim($image);

        return $normalized !== '' ? $normalized : null;
    }
}
