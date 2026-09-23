<?php

namespace App\Service\CompanyPost;

use App\Entity\CompanyPost;
use App\Entity\PostComments;
use App\Entity\PostReaction;

class CompanyPostResponseBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function buildItem(CompanyPost $post): array
    {
        $sanitizedImage = $this->sanitizeImageValue($post->getImageUrl());
        $commentList = $this->buildCommentList($post);

        return [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'author' => $post->getAuthor(),
            'authorId' => $post->getAuthorId()?->getId(),
            'companyName' => $post->getCompanyName(),
            'companyId' => $post->getCompanyId()?->getId(),
            'likes' => $this->countLikes($post),
            'comments' => count($commentList),
            'commentList' => $commentList,
            'image' => $sanitizedImage,
            'imageUrl' => $sanitizedImage,
            'visibility' => $post->getVisibility(),
            'category' => $post->getCategory(),
            'postedAt' => $post->getPostedAt()?->format(\DateTimeInterface::ATOM),
            'publiedAt' => $post->getPubliedAt()?->format(\DateTimeInterface::ATOM),
            'createdAt' => $post->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'updatedAt' => $post->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * @param array<int, CompanyPost> $posts
     * @return array<int, array<string, mixed>>
     */
    public function buildCollection(array $posts): array
    {
        $items = [];

        foreach ($posts as $post) {
            if (!$post instanceof CompanyPost) {
                continue;
            }

            $items[] = $this->buildItem($post);
        }

        return $items;
    }

    private function countLikes(CompanyPost $post): int
    {
        $likes = 0;

        foreach ($post->getPostReactions() as $reaction) {
            if (!$reaction instanceof PostReaction) {
                continue;
            }

            if (strtolower((string) $reaction->getType()) === 'like') {
                $likes++;
            }
        }

        return $likes;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildCommentList(CompanyPost $post): array
    {
        $result = [];

        foreach ($post->getPostComments() as $comment) {
            if (!$comment instanceof PostComments) {
                continue;
            }

            $result[] = [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'authorId' => $comment->getAuthorId()?->getId(),
                'author' => $comment->getAuthorId()?->getEmail(),
                'createdAt' => $comment->getCreatedAt()?->format(\DateTimeInterface::ATOM),
                'updatedAt' => $comment->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
            ];
        }

        return $result;
    }

    private function sanitizeImageValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim($value);
        if ($normalized === '') {
            return null;
        }

        if (str_starts_with($normalized, '/')) {
            return $normalized;
        }

        if (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://')) {
            return filter_var($normalized, FILTER_VALIDATE_URL) !== false ? $normalized : null;
        }

        if (!preg_match('#^data:image/[a-zA-Z0-9.+-]+;base64,[A-Za-z0-9+/=\r\n]+$#', $normalized)) {
            return null;
        }

        $parts = explode(',', $normalized, 2);
        if (count($parts) !== 2) {
            return null;
        }

        $decoded = base64_decode(str_replace(["\r", "\n"], '', $parts[1]), true);

        return $decoded !== false ? $normalized : null;
    }
}
