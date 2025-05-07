<?php

namespace App\Controllers;

use App\Models\Resource;
use App\Core\Response;
use App\Core\Request;
use App\Core\Auth;
use App\Core\ResourceManager;
use App\Core\MemoryManager;
use App\Core\SQLFileProcessor;
use App\Core\FileValidator;

class ResourceController extends BaseController {
    protected $resource;
    protected $resourceManager;

    public function __construct() {
        parent::__construct();
        $this->resource = new Resource();
        $this->resourceManager = ResourceManager::getInstance();
    }

    public function index(Request $request) {
        try {
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);
            $filters = [
                'user_id' => $request->get('user_id'),
                'visibility' => $request->get('visibility'),
                'status' => $request->get('status')
            ];

            $resources = $this->resource->findAll($page, $limit, $filters);
            return $this->response->json(['data' => $resources]);
        } catch (\Exception $e) {
            return $this->response->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, $id) {
        try {
            $resource = $this->resource->findById($id);
            
            if (!$resource) {
                return $this->response->json(['error' => '리소스를 찾을 수 없습니다.'], 404);
            }

            // 비공개 리소스인 경우 권한 확인
            if ($resource['visibility'] === 'private') {
                $user = $this->auth->user();
                if (!$user || ($user['id'] !== $resource['user_id'] && !$user['is_admin'])) {
                    return $this->response->json(['error' => '접근 권한이 없습니다.'], 403);
                }
            }

            return $this->response->json(['data' => $resource]);
        } catch (\Exception $e) {
            return $this->response->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request) {
        try {
            $user = $this->auth->user();
            if (!$user) {
                return $this->response->json(['error' => '로그인이 필요합니다.'], 401);
            }

            // 입력 데이터 검증
            $data = $this->validateResourceData($request);
            if (isset($data['error'])) {
                return $this->response->json(['error' => $data['error']], 422);
            }

            $data['user_id'] = $user['id'];
            $resourceId = $this->resource->create($data);

            return $this->response->json([
                'message' => '리소스가 생성되었습니다.',
                'data' => ['id' => $resourceId]
            ], 201);
        } catch (\Exception $e) {
            return $this->response->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id) {
        try {
            $user = $this->auth->user();
            if (!$user) {
                return $this->response->json(['error' => '로그인이 필요합니다.'], 401);
            }

            $resource = $this->resource->findById($id);
            if (!$resource) {
                return $this->response->json(['error' => '리소스를 찾을 수 없습니다.'], 404);
            }

            // 권한 확인
            if ($resource['user_id'] !== $user['id'] && !$user['is_admin']) {
                return $this->response->json(['error' => '수정 권한이 없습니다.'], 403);
            }

            // 입력 데이터 검증
            $data = $this->validateResourceData($request, true);
            if (isset($data['error'])) {
                return $this->response->json(['error' => $data['error']], 422);
            }

            $this->resource->update($id, $data);

            return $this->response->json(['message' => '리소스가 수정되었습니다.']);
        } catch (\Exception $e) {
            return $this->response->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id) {
        try {
            $user = $this->auth->user();
            if (!$user) {
                return $this->response->json(['error' => '로그인이 필요합니다.'], 401);
            }

            $resource = $this->resource->findById($id);
            if (!$resource) {
                return $this->response->json(['error' => '리소스를 찾을 수 없습니다.'], 404);
            }

            // 권한 확인
            if ($resource['user_id'] !== $user['id'] && !$user['is_admin']) {
                return $this->response->json(['error' => '삭제 권한이 없습니다.'], 403);
            }

            $this->resource->delete($id);

            return $this->response->json(['message' => '리소스가 삭제되었습니다.']);
        } catch (\Exception $e) {
            return $this->response->json(['error' => $e->getMessage()], 500);
        }
    }

    private function validateResourceData(Request $request, $isUpdate = false) {
        $data = [];
        $errors = [];

        if (!$isUpdate || $request->has('title')) {
            $title = trim($request->get('title'));
            if (empty($title)) {
                $errors[] = '제목은 필수입니다.';
            }
            $data['title'] = $title;
        }

        if (!$isUpdate || $request->has('content')) {
            $content = trim($request->get('content'));
            if (empty($content)) {
                $errors[] = '내용은 필수입니다.';
            }
            $data['content'] = $content;
        }

        if (!$isUpdate || $request->has('description')) {
            $description = trim($request->get('description'));
            if (empty($description)) {
                $errors[] = '설명은 필수입니다.';
            }
            $data['description'] = $description;
        }

        if ($request->has('visibility')) {
            $visibility = $request->get('visibility');
            if (!in_array($visibility, ['public', 'private'])) {
                $errors[] = '공개 여부는 public 또는 private만 가능합니다.';
            }
            $data['visibility'] = $visibility;
        }

        if ($request->has('status')) {
            $status = $request->get('status');
            if (!in_array($status, ['draft', 'published'])) {
                $errors[] = '상태는 draft 또는 published만 가능합니다.';
            }
            $data['status'] = $status;
        }

        if ($request->has('tags')) {
            $tags = $request->get('tags');
            if (!is_array($tags)) {
                $errors[] = '태그는 배열 형식이어야 합니다.';
            }
            $data['tags'] = $tags;
        }

        if (!empty($errors)) {
            return ['error' => implode(' ', $errors)];
        }

        return $data;
    }
} 