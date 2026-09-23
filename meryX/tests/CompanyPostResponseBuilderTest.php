<?php

namespace App\Tests;

use App\Entity\Company;
use App\Entity\CompanyPost;
use App\Entity\PostComments;
use App\Entity\PostReaction;
use App\Entity\User;
use App\Entity\UserType;
use App\Service\CompanyPost\CompanyPostResponseBuilder;
use PHPUnit\Framework\TestCase;

final class CompanyPostResponseBuilderTest extends TestCase
{
    public function testBuildItemNormalizesContractAndSanitizesInvalidImage(): void
    {
        $company = new Company();
        $company->setName('Tech Co');
        $company->setLogo('logo');
        $company->setSector('tech');
        $company->setRankingScore('1');
        $company->setIsApproved(true);
        $company->setDescription('desc');
        $company->setStatus('approved');
        $company->setRegistrationNumber('reg');
        $company->setTaxId('tax');
        $company->setVerifiedAt(new \DateTimeImmutable());
        $company->setCreatedAt(new \DateTimeImmutable('-5 days'));
        $company->setUpdatedAt(new \DateTimeImmutable('-1 day'));
        $this->setEntityId($company, 5);

        $author = new User();
        $type = new UserType();
        $type->setName('COMPANY');
        $type->setPermission([]);
        $type->setDescription('company');
        $type->setIsEnabled(true);
        $type->setIsDeleted(false);
        $author->setUserTypeId($type);
        $author->setEmail('author@company.test');
        $this->setEntityId($author, 6);

        $post = new CompanyPost();
        $post->setCompanyId($company);
        $post->setAuthorId($author);
        $post->setTitle('Post title');
        $post->setContent('Post content');
        $post->setCategory('news');
        $post->setVisibility('public');
        $post->setImageUrl('not-a-valid-image-payload');
        $post->setPubliedAt(new \DateTimeImmutable('-1 hour'));
        $post->setCreatedAt(new \DateTimeImmutable('-2 hours'));
        $post->setUpdatedAt(new \DateTimeImmutable('-30 minutes'));
        $this->setEntityId($post, 10);

        $like = new PostReaction();
        $like->setPostId($post);
        $like->setUserId($author);
        $like->setType('like');
        $like->setCreatedAt(new \DateTimeImmutable('-10 minutes'));

        $comment = new PostComments();
        $comment->setPostId($post);
        $comment->setAuthorId($author);
        $comment->setContent('Great post');
        $comment->setCreatedAt(new \DateTimeImmutable('-15 minutes'));
        $comment->setUpdatedAt(new \DateTimeImmutable('-14 minutes'));

        $post->addPostReaction($like);
        $post->addPostComment($comment);

        $builder = new CompanyPostResponseBuilder();
        $payload = $builder->buildItem($post);

        $this->assertSame(10, $payload['id']);
        $this->assertSame('Post title', $payload['title']);
        $this->assertSame('author@company.test', $payload['author']);
        $this->assertSame('Tech Co', $payload['companyName']);
        $this->assertSame(1, $payload['likes']);
        $this->assertSame(1, $payload['comments']);
        $this->assertCount(1, $payload['commentList']);
        $this->assertSame('Great post', $payload['commentList'][0]['content']);
        $this->assertNull($payload['image']);
        $this->assertNull($payload['imageUrl']);
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, $id);
    }
}
