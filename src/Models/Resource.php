<?php
namespace App\Models;

use PDO;
use PDOException;
use Exception;
use App\Core\Database;
use App\Core\Model;
use App\Exceptions\ModelException;
use App\Core\Language;

error_log("Resource.php loaded from: " . __FILE__); // 파일 로드 확인 로그

/**
 * 리소스 모델 클래스
 */
class Resource extends Model {
    protected $table = 'resources';
    protected $fillable = [
        'user_id', 'url', 'visibility', 'status', 'view_count',
        'like_count', 'comment_count', 'published_at',
        'file_path', 'slug',
        'link', 'category', 'type'
    ];

    public function __construct() {
        parent::__construct(Database::getInstance());
    }

    public function getTranslation($id, $language = null)
    {
        if (!$language) {
            $language = Language::getInstance()->getCurrentLang();
        }
        $defaultLang = 'en'; // Using 'en' as the default fallback language
        $sql = "SELECT 
                rt.title as title,
                rt.content as content,
                rt.description as description
            FROM resources r
            LEFT JOIN resource_translations rt ON r.id = rt.resource_id AND rt.language_code = ?
            WHERE r.id = ?";
        return $this->db->fetch($sql, [$language, $id]);
    }

    /**
     * 언어별 번역 정보 조인 쿼리 생성 (공통)
     */
    private function translationSelect($alias = 'r', $lang = null, $def = null) {
        if (!$lang) $lang = Language::getInstance()->getCurrentLang();
        return [
            "rt.title as title",
            "rt.content as content",
            "rt.description as description",
            "LEFT JOIN resource_translations rt ON {$alias}.id = rt.resource_id AND rt.language_code = ?"
        ];
    }

    /**
     * 리소스 목록 (태그 포함, 다국어)
     */
    public function getResourcesWithTags($limit = null, $offset = null, $language = null, $defaultLang = null) {
        try {
            $lang = $language ?: Language::getInstance()->getCurrentLang();
            $def = $defaultLang ?: 'en'; // Using 'en' as the default fallback language
            $select = $this->translationSelect('r', $lang);
            $sql = "SELECT r.*, {$select[0]}, {$select[1]}, {$select[2]}, GROUP_CONCAT(t.name) as tags
                    FROM {$this->table} r
                    {$select[3]}
                    LEFT JOIN resource_tags rtag ON r.id = rtag.resource_id
                    LEFT JOIN tags t ON rtag.tag_id = t.id
                    WHERE EXISTS (
                        SELECT 1 FROM resource_translations rt 
                        WHERE rt.resource_id = r.id 
                        AND rt.language_code = ?
                    )
                    GROUP BY r.id
                    ORDER BY r.created_at DESC";
            if ($limit !== null) {
                $sql .= " LIMIT ?";
                if ($offset !== null) {
                    $sql .= " OFFSET ?";
                }
            }
            $params = [$lang];
            if ($limit !== null) {
                $params[] = $limit;
                if ($offset !== null) $params[] = $offset;
            }
            $resources = $this->db->fetchAll($sql, $params);
            foreach ($resources as &$resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
                // 번역된 title과 content를 메인 필드로 설정
                $resource['title'] = $resource['title'] ?? '';
                $resource['content'] = $resource['content'] ?? '';
            }
            return $resources;
        } catch (PDOException $e) {
            error_log("Database error in getResourcesWithTags: " . $e->getMessage());
            throw new Exception("리소스 목록을 조회하는 중 오류가 발생했습니다.");
        }
    }

    public function getResourceWithTags($id) {
        try {
            $sql = "SELECT r.*, GROUP_CONCAT(t.name) as tags 
                    FROM {$this->table} r 
                    LEFT JOIN resource_tags rt ON r.id = rt.resource_id 
                    LEFT JOIN tags t ON rt.tag_id = t.id 
                    WHERE r.id = ? 
                    GROUP BY r.id";

            $resource = $this->db->fetch($sql, [$id]);

            if ($resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
            }

            return $resource;
        } catch (PDOException $e) {
            error_log("Database error in getResourceWithTags: " . $e->getMessage());
            throw new Exception("리소스 상세 정보를 조회하는 중 오류가 발생했습니다.");
        }
    }

    public function getRelatedResources($resource_id, $limit = 3) {
        try {
            $sql = "SELECT r.*, GROUP_CONCAT(t.name) as tags 
                    FROM {$this->table} r 
                    LEFT JOIN resource_tags rt ON r.id = rt.resource_id 
                    LEFT JOIN tags t ON rt.tag_id = t.id 
                    WHERE r.id != ? 
                    AND r.id IN (
                        SELECT r2.id 
                        FROM {$this->table} r2 
                        JOIN resource_tags rt2 ON r2.id = rt2.resource_id 
                        WHERE rt2.tag_id IN (
                            SELECT tag_id 
                            FROM resource_tags 
                            WHERE resource_id = ?
                        )
                    )
                    GROUP BY r.id 
                    ORDER BY r.created_at DESC 
                    LIMIT ?";

            $resources = $this->db->fetchAll($sql, [$resource_id, $resource_id, $limit]);

            foreach ($resources as &$resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
            }

            return $resources;
        } catch (PDOException $e) {
            error_log("Database error in getRelatedResources: " . $e->getMessage());
            throw new Exception("관련 리소스를 조회하는 중 오류가 발생했습니다.");
        }
    }

    public function searchResources($query, $limit = null, $offset = null) {
        try {
            $lang = Language::getInstance()->getCurrentLang();
            $sql = "SELECT r.*, rt.title, rt.content, rt.description, GROUP_CONCAT(t.name) as tags 
                    FROM resources r 
                    LEFT JOIN resource_translations rt ON r.id = rt.resource_id AND rt.language_code = ?
                    LEFT JOIN resource_tags rtag ON r.id = rtag.resource_id 
                    LEFT JOIN tags t ON rtag.tag_id = t.id 
                    WHERE rt.title LIKE ? 
                    OR rt.content LIKE ? 
                    OR rt.description LIKE ?
                    GROUP BY r.id 
                    ORDER BY r.created_at DESC";

            if ($limit !== null) {
                $sql .= " LIMIT ?";
                if ($offset !== null) {
                    $sql .= " OFFSET ?";
                }
            }

            $searchTerm = '%' . $query . '%';
            $params = [$lang, $searchTerm, $searchTerm, $searchTerm];
            
            if ($limit !== null) {
                $params[] = $limit;
                if ($offset !== null) {
                    $params[] = $offset;
                }
            }

            $resources = $this->db->fetchAll($sql, $params);

            foreach ($resources as &$resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
            }

            return $resources;
        } catch (PDOException $e) {
            error_log("Database error in searchResources: " . $e->getMessage());
            throw new Exception("리소스 검색 중 오류가 발생했습니다: " . $e->getMessage());
        }
    }

    /**
     * 리소스 검색 (다국어, FULLTEXT)
     */
    public function searchFulltext($keyword, $language = null, $filters = []) {
        try {
            $lang = $language ?: Language::getInstance()->getCurrentLang();
            $def = 'en'; // Using 'en' as the default fallback language
            $select = $this->translationSelect('r', $lang, $def);
            $params = [$lang, $def];
            $where = ["rt.language_code = ?"];
            
            if ($keyword) {
                $where[] = "(
                    MATCH(rt.title, rt.content, rt.description) AGAINST (? IN BOOLEAN MODE) OR
                    MATCH(u.name) AGAINST (? IN BOOLEAN MODE) OR
                    MATCH(r.slug, r.category) AGAINST (? IN BOOLEAN MODE) OR
                    EXISTS (
                        SELECT 1 FROM tags t 
                        JOIN resource_tags rt2 ON t.id = rt2.tag_id 
                        WHERE rt2.resource_id = r.id 
                        AND MATCH(t.name) AGAINST (? IN BOOLEAN MODE)
                    )
                )";
                $params[] = $keyword;
                $params[] = $keyword;
                $params[] = $keyword;
                $params[] = $keyword;
            }
            
            if (!empty($filters['tags'])) {
                $placeholders = str_repeat('?,', count($filters['tags']) - 1) . '?';
                $where[] = "EXISTS (SELECT 1 FROM resource_tags rt2 WHERE rt2.resource_id = r.id AND rt2.tag_id IN ($placeholders))";
                $params = array_merge($params, $filters['tags']);
            }
            
            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $sql = "SELECT r.*, {$select[0]}, {$select[1]}, {$select[2]},
                    u.name as username,
                    GROUP_CONCAT(DISTINCT t.name) as tags,
                    (
                        MATCH(rt.title) AGAINST (? IN BOOLEAN MODE) * 3 +
                        MATCH(rt.content, rt.description) AGAINST (? IN BOOLEAN MODE) * 2 +
                        MATCH(u.name) AGAINST (? IN BOOLEAN MODE) * 1.5 +
                        MATCH(r.slug, r.category) AGAINST (? IN BOOLEAN MODE) * 1.5 +
                        (
                            SELECT COUNT(*) * 1.5
                            FROM tags t2
                            JOIN resource_tags rt2 ON t2.id = rt2.tag_id
                            WHERE rt2.resource_id = r.id
                            AND MATCH(t2.name) AGAINST (? IN BOOLEAN MODE)
                        )
                    ) as relevance
                FROM resources r
                {$select[3]}
                {$select[4]}
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN resource_tags rtag ON r.id = rtag.resource_id
                LEFT JOIN tags t ON rtag.tag_id = t.id
                $whereClause
                GROUP BY r.id
                HAVING relevance > 0
                ORDER BY relevance DESC, r.created_at DESC";
                
            if ($keyword) {
                $params[] = $keyword;
                $params[] = $keyword;
                $params[] = $keyword;
                $params[] = $keyword;
                $params[] = $keyword;
            }
            
            $resources = $this->db->fetchAll($sql, $params);
            foreach ($resources as &$resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
            }
            return $resources;
        } catch (PDOException $e) {
            error_log("Database error in searchFulltext: " . $e->getMessage());
            throw new Exception("리소스 검색 중 오류가 발생했습니다: " . $e->getMessage());
        }
    }

    /**
     * 모든 태그 조회
     */
    public function getAllTags() {
        try {
            $sql = "SELECT DISTINCT t.* 
                    FROM tags t 
                    JOIN resource_tags rt ON t.id = rt.tag_id 
                    ORDER BY t.name";
            return $this->db->fetchAll($sql);
        } catch (PDOException $e) {
            error_log("Database error in getAllTags: " . $e->getMessage());
            throw new Exception("태그 목록을 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * ID로 리소스 조회 (다국어)
     */
    public function findById($id, $language = null, $defaultLang = null) {
        try {
            $lang = $language ?: Language::getInstance()->getCurrentLang();
            $def = $defaultLang ?: 'en'; // Using 'en' as the default fallback language
            $select = $this->translationSelect('r', $lang);
            $sql = "SELECT r.*, rt.language_code as translation_language_code, {$select[0]}, {$select[1]}, {$select[2]},
                    GROUP_CONCAT(DISTINCT t.name) as tags,
                    GROUP_CONCAT(DISTINCT t.id) as tag_ids,
                    u.name as author_name
                FROM resources r
                {$select[3]}
                LEFT JOIN resource_tags rtag ON r.id = rtag.resource_id
                LEFT JOIN tags t ON rtag.tag_id = t.id
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.id = ?
                GROUP BY r.id";
            $resource = $this->db->fetch($sql, [$lang, $id]);
            if ($resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
                $resource['tag_ids'] = $resource['tag_ids'] ? explode(',', $resource['tag_ids']) : [];
            }
            return $resource;
        } catch (Exception $e) {
            error_log("Error in Resource::findById: " . $e->getMessage());
            throw new Exception("리소스를 찾을 수 없습니다.");
        }
    }

    /**
     * 리소스의 태그 조회
     */
    public function getTags($resource_id) {
        try {
            $sql = "SELECT t.* 
                    FROM tags t 
                    JOIN resource_tags rt ON t.id = rt.tag_id 
                    WHERE rt.resource_id = ?";
            return $this->db->fetchAll($sql, [$resource_id]);
        } catch (PDOException $e) {
            error_log("Database error in getTags: " . $e->getMessage());
            throw new Exception("리소스의 태그를 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 태그 이름으로 태그 찾기 또는 생성
     */
    public function findOrCreateTag($name) {
        try {
            // 태그 이름 정규화
            $name = trim($name);
            if (empty($name)) {
                throw new Exception("태그 이름이 비어있습니다.");
            }

            // 태그 찾기
            $sql = "SELECT * FROM tags WHERE name = ?";
            $tag = $this->db->fetch($sql, [$name]);

            // 태그가 없으면 생성
            if (!$tag) {
                $sql = "INSERT INTO tags (name, created_at) VALUES (?, NOW())";
                $this->db->query($sql, [$name]);
                $tag_id = $this->db->lastInsertId();
                $tag = ['id' => $tag_id, 'name' => $name];
            }

            return $tag;
        } catch (PDOException $e) {
            error_log("Database error in findOrCreateTag: " . $e->getMessage());
            throw new Exception("태그를 처리하는 중 오류가 발생했습니다: " . $e->getMessage());
        }
    }

    /**
     * 리소스에 태그 추가
     */
    public function addTag($resource_id, $tag_name) {
        try {
            $this->db->beginTransaction();

            // 태그 찾기 또는 생성
            $tag = $this->findOrCreateTag($tag_name);

            // 리소스에 태그 추가
            $sql = "INSERT IGNORE INTO resource_tags (resource_id, tag_id) VALUES (?, ?)";
            $this->db->query($sql, [$resource_id, $tag['id']]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("Database error in addTag: " . $e->getMessage());
            throw new Exception("태그를 추가하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스에서 태그 제거
     */
    public function removeTag($resource_id, $tag_id) {
        try {
            $sql = "DELETE FROM resource_tags WHERE resource_id = ? AND tag_id = ?";
            $this->db->query($sql, [$resource_id, $tag_id]);
            return true;
        } catch (PDOException $e) {
            error_log("Database error in removeTag: " . $e->getMessage());
            throw new Exception("태그를 제거하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 공개/비공개 상태 변경
     */
    public function updateVisibility($resource_id, $is_private) {
        try {
            $sql = "UPDATE {$this->table} SET is_private = ? WHERE id = ?";
            $this->db->query($sql, [$is_private, $resource_id]);
            return true;
        } catch (PDOException $e) {
            error_log("Database error in updateVisibility: " . $e->getMessage());
            throw new Exception("리소스의 공개 여부를 업데이트하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 생성 (수정됨: title, content, description 제거)
     */
    public function create(array $data): ?int
    {
        try {
            $this->db->beginTransaction();

            // 리소스 기본 정보 저장
            $publishedAt = (isset($data['status']) && $data['status'] === 'published') ? date('Y-m-d H:i:s') : null;
            $sql = "INSERT INTO resources (
                user_id, file_path, visibility, status, slug, published_at, link, category, type, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $params = [
                $data['user_id'],
                $data['file_path'] ?? null,
                $data['visibility'] ?? 'public',
                $data['status'] ?? 'draft',
                $data['slug'],
                $publishedAt,
                $data['link'] ?? null,
                $data['category'] ?? null,
                $data['type'] ?? null
            ];

            error_log('[DEBUG] Resource INSERT SQL: ' . $sql);
            error_log('[DEBUG] Resource INSERT Params: ' . json_encode($params));
            
            // 쿼리 실행 및 결과 확인
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            if (!$result) {
                error_log('[ERROR] Resource insert failed. Error: ' . json_encode($stmt->errorInfo()));
                throw new \Exception('리소스 저장 실패: ' . implode(', ', $stmt->errorInfo()));
            }

            // 생성된 ID 확인
            $resourceId = $this->db->lastInsertId();
            error_log('[DEBUG] Generated Resource ID: ' . $resourceId);

            if (!$resourceId) {
                // ID가 없는 경우 slug로 재시도
                $checkSql = "SELECT id FROM resources WHERE slug = ? AND user_id = ? ORDER BY id DESC LIMIT 1";
                $checkStmt = $this->db->prepare($checkSql);
                $checkStmt->execute([$data['slug'], $data['user_id']]);
                $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($row && isset($row['id'])) {
                    $resourceId = $row['id'];
                    error_log('[DEBUG] Retrieved Resource ID from slug: ' . $resourceId);
                } else {
                    error_log('[ERROR] Failed to get resource ID. Slug: ' . $data['slug']);
                    throw new \Exception('리소스 ID를 생성할 수 없습니다.');
                }
            }

            // 번역 데이터 저장
            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $lang => $translation) {
                    $translationSql = "INSERT INTO resource_translations (
                        resource_id, language_code, title, content, description
                    ) VALUES (?, ?, ?, ?, ?)";
                    
                    $translationParams = [
                        $resourceId,
                        $lang,
                        $translation['title'],
                        $translation['content'] ?? null,
                        $translation['description'] ?? null
                    ];
                    
                    error_log('[DEBUG] Translation INSERT SQL: ' . $translationSql);
                    error_log('[DEBUG] Translation INSERT Params: ' . json_encode($translationParams));
                    
                    $translationStmt = $this->db->prepare($translationSql);
                    $translationResult = $translationStmt->execute($translationParams);
                    
                    if (!$translationResult) {
                        error_log('[ERROR] Translation insert failed. Error: ' . json_encode($translationStmt->errorInfo()));
                        throw new \Exception('번역 데이터 저장 실패: ' . implode(', ', $translationStmt->errorInfo()));
                    }
                }
            }

            // 태그 처리
            if (!empty($data['tags'])) {
                foreach ($data['tags'] as $tagName) {
                    // 태그 생성 또는 조회
                    $tagSql = "INSERT IGNORE INTO tags (name, created_at) VALUES (?, NOW())";
                    $tagStmt = $this->db->prepare($tagSql);
                    $tagStmt->execute([$tagName]);
                    
                    // 태그 ID 조회
                    $tagIdSql = "SELECT id FROM tags WHERE name = ?";
                    $tagIdStmt = $this->db->prepare($tagIdSql);
                    $tagIdStmt->execute([$tagName]);
                    $tagResult = $tagIdStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($tagResult && isset($tagResult['id'])) {
                        $tagId = $tagResult['id'];
                        // 리소스-태그 관계 생성
                        $relationSql = "INSERT IGNORE INTO resource_tags (resource_id, tag_id) VALUES (?, ?)";
                        $relationStmt = $this->db->prepare($relationSql);
                        $relationResult = $relationStmt->execute([$resourceId, $tagId]);
                        
                        if (!$relationResult) {
                            error_log('[ERROR] Tag relation insert failed. Error: ' . json_encode($relationStmt->errorInfo()));
                            throw new \Exception('태그 관계 저장 실패: ' . implode(', ', $relationStmt->errorInfo()));
                        }
                    }
                }
            }

            $this->db->commit();
            return (int)$resourceId;
        } catch (\Exception $e) {
            $pdo = $this->db->getConnection();
            if (method_exists($pdo, 'inTransaction') && $pdo->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error in Resource::create: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * 리소스 수정 (다국어)
     */
    public function update($id, array $data): ?array {
        try {
            $this->db->beginTransaction();

            // 기본 정보 업데이트 (title, content, description 제외)
            $updateFields = [];
            $params = [];
            foreach ($this->fillable as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }

            // status 변경에 따라 published_at 처리
            if (isset($data['status'])) {
                $currentStatus = $this->db->fetch("SELECT status FROM resources WHERE id = ?", [$id])['status'];
                if ($currentStatus === 'draft' && $data['status'] === 'published') {
                    $updateFields[] = "published_at = ?";
                    $params[] = date('Y-m-d H:i:s');
                } elseif ($currentStatus === 'published' && $data['status'] === 'draft') {
                    $updateFields[] = "published_at = ?";
                    $params[] = null;
                }
            }

            if ($updateFields) {
                $params[] = $id;
                $sql = "UPDATE resources SET " . implode(', ', $updateFields) . " WHERE id = ?";
                $this->db->query($sql, $params);
            }

            // 번역 데이터 업데이트
            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $lang => $translation) {
                    $sql = "INSERT INTO resource_translations (resource_id, language_code, title, content, description) 
                            VALUES (?, ?, ?, ?, ?) 
                            ON DUPLICATE KEY UPDATE 
                            title = VALUES(title), 
                            content = VALUES(content), 
                            description = VALUES(description)";
                    $this->db->query($sql, [
                        $id,
                        $lang,
                        $translation['title'],
                        $translation['content'] ?? null,
                        $translation['description'] ?? null
                    ]);
                }
            }

            // 태그 업데이트
            if (isset($data['tags'])) {
                $tag_names = is_array($data['tags']) ? $data['tags'] : array_filter(array_map('trim', explode(',', $data['tags'])));
                $this->updateResourceTags($id, $tag_names);
            }

            $this->db->commit();
            return $this->findById($id);
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error in Resource::update: " . $e->getMessage());
            throw new Exception("리소스 업데이트 중 오류가 발생했습니다: " . $e->getMessage());
        }
    }

    /**
     * 리소스 삭제
     */
    public function delete($id): bool
    {
        try {
            $this->db->beginTransaction();

            // 태그 카운트 감소를 위해 태그 ID 저장
            $tagIds = $this->db->fetchAll(
                "SELECT tag_id FROM resource_tags WHERE resource_id = ?",
                [$id],
                PDO::FETCH_COLUMN
            );

            // 리소스 관련 데이터 삭제
            $this->db->query("DELETE FROM resource_translations WHERE resource_id = ?", [$id]);
            $this->db->query("DELETE FROM resource_tags WHERE resource_id = ?", [$id]);
            $this->db->query("DELETE FROM resources WHERE id = ?", [$id]);

            // 태그 카운트 업데이트
            if ($tagIds) {
                foreach ($tagIds as $tagId) {
                    $this->db->query(
                        "UPDATE tags SET count = count - 1 WHERE id = ? AND count > 0",
                        [$tagId]
                    );
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error in Resource::delete: " . $e->getMessage());
            throw new Exception("리소스 삭제 중 오류가 발생했습니다.");
        }
    }

    /**
     * 전체 목록 (다국어)
     */
    public function findAll($language = null, $defaultLang = null) {
        try {
            $lang = $language ?: Language::getInstance()->getCurrentLang();
            $def = $defaultLang ?: 'en'; // Using 'en' as the default fallback language
            $select = $this->translationSelect('r', $lang);
            $sql = "SELECT r.*, {$select[0]}, {$select[1]}, {$select[2]}, GROUP_CONCAT(t.name) as tags FROM {$this->table} r {$select[3]} LEFT JOIN resource_tags rtag ON r.id = rtag.resource_id LEFT JOIN tags t ON rtag.tag_id = t.id GROUP BY r.id ORDER BY r.created_at DESC";
            $resources = $this->db->fetchAll($sql, [$lang]);
            foreach ($resources as &$resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
            }
            return $resources;
        } catch (PDOException $e) {
            error_log("Error in findAll: " . $e->getMessage());
            throw new Exception("리소스 목록을 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스의 태그 ID 목록 조회
     */
    public function getResourceTagIds($resource_id) {
        try {
            $sql = "SELECT tag_id FROM resource_tags WHERE resource_id = ?";
            return $this->db->fetchAll($sql, [$resource_id], PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error in getResourceTagIds: " . $e->getMessage());
            throw new Exception("리소스의 태그 ID를 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스의 태그를 업데이트합니다.
     *
     * @param int $resourceId 리소스 ID
     * @param array $tagNames 태그 이름 배열
     * @return bool 성공 여부
     */
    public function updateResourceTags(int $resourceId, array $tagNames): bool
    {
        try {
            $this->db->beginTransaction();

            // 1. 기존 태그 관계 삭제
            $stmt = $this->db->prepare("DELETE FROM resource_tags WHERE resource_id = ?");
            $stmt->execute([$resourceId]);

            // 2. 각 태그 처리
            foreach ($tagNames as $tagName) {
                $tagName = trim($tagName);
                if (empty($tagName)) continue;

                try {
                    // 2.1 태그 존재 여부 확인
                    $stmt = $this->db->prepare("SELECT id FROM tags WHERE name = ?");
                    $stmt->execute([$tagName]);
                    $tag = $stmt->fetch();

                    if (!$tag) {
                        // 2.2 태그가 없으면 생성
                        $stmt = $this->db->prepare("INSERT INTO tags (name, created_at) VALUES (?, NOW())");
                        $stmt->execute([$tagName]);
                        
                        // 생성된 태그 ID 조회
                        $stmt = $this->db->prepare("SELECT id FROM tags WHERE name = ? ORDER BY id DESC LIMIT 1");
                        $stmt->execute([$tagName]);
                        $tag = $stmt->fetch();
                        
                        if (!$tag) {
                            error_log("Failed to retrieve tag ID after creation: " . $tagName);
                            throw new \Exception("Failed to retrieve tag ID after creation: " . $tagName);
                        }
                    }

                    // 2.3 리소스-태그 관계 생성
                    $stmt = $this->db->prepare("INSERT INTO resource_tags (resource_id, tag_id, created_at) VALUES (?, ?, NOW())");
                    $result = $stmt->execute([$resourceId, $tag['id']]);
                    
                    if (!$result) {
                        error_log("Failed to create resource-tag relationship for tag: " . $tagName);
                        throw new \Exception("Failed to create resource-tag relationship for tag: " . $tagName);
                    }
                } catch (\Exception $e) {
                    error_log("Error processing tag '{$tagName}': " . $e->getMessage());
                    throw $e;
                }
            }

            // 3. 태그 카운트 업데이트
            $stmt = $this->db->prepare("
                UPDATE tags t 
                SET count = (
                    SELECT COUNT(DISTINCT rt.resource_id) 
                    FROM resource_tags rt 
                    WHERE rt.tag_id = t.id
                )
            ");
            $stmt->execute();

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error in updateResourceTags: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 리소스 조회수 증가 및 조회 기록
     */
    public function recordView($resource_id, $viewer_id) {
        try {
            $this->db->beginTransaction();

            // 이미 조회한 기록이 있는지 확인
            $sql = "SELECT id FROM resource_views 
                    WHERE resource_id = ? AND viewer_id = ? 
                    AND DATE(viewed_at) = CURDATE()";
            $existing = $this->db->fetch($sql, [$resource_id, $viewer_id]);

            if (!$existing) {
                // 새로운 조회 기록 추가
                $sql = "INSERT INTO resource_views (resource_id, viewer_id, viewed_at) 
                        VALUES (?, ?, NOW())";
                $this->db->query($sql, [$resource_id, $viewer_id]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("Error in recordView: " . $e->getMessage());
            throw new Exception("조회수를 기록하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 평점 등록/수정
     */
    public function rateResource($resource_id, $viewer_id, $rating) {
        try {
            if ($rating < 1 || $rating > 5) {
                throw new Exception("평점은 1에서 5 사이여야 합니다.");
            }

            $this->db->beginTransaction();

            // 기존 평점 업데이트 또는 새로운 평점 추가
            $sql = "INSERT INTO resource_ratings (resource_id, user_id, rating, created_at) 
                    VALUES (?, ?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE rating = ?";
            
            $this->db->query($sql, [$resource_id, $viewer_id, $rating, $rating]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("Error in rateResource: " . $e->getMessage());
            throw new Exception("평점을 등록하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스의 평균 평점 조회
     */
    public function getAverageRating($resource_id) {
        try {
            $sql = "SELECT AVG(rating) as avg_rating, COUNT(rating) as rating_count
                    FROM resource_ratings
                    WHERE resource_id = ? AND rating IS NOT NULL";
            
            $result = $this->db->fetch($sql, [$resource_id]);
            return [
                'average' => round($result['avg_rating'] ?? 0, 1),
                'count' => (int)($result['rating_count'] ?? 0)
            ];
        } catch (PDOException $e) {
            error_log("Error in getAverageRating: " . $e->getMessage());
            throw new Exception("평균 평점을 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스의 조회수 조회
     */
    public function getViewCount($resource_id) {
        try {
            $sql = "SELECT COUNT(DISTINCT viewer_id) as view_count
                    FROM resource_views
                    WHERE resource_id = ?";
            
            $result = $this->db->fetch($sql, [$resource_id]);
            return (int)($result['view_count'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error in getViewCount: " . $e->getMessage());
            throw new Exception("조회수를 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 사용자의 리소스 평점 조회
     */
    public function getUserRating($resource_id, $user_id) {
        try {
            $sql = "SELECT rating
                    FROM resource_ratings
                    WHERE resource_id = ? AND user_id = ?";
            
            $result = $this->db->fetch($sql, [$resource_id, $user_id]);
            return $result ? (int)$result['rating'] : null;
        } catch (PDOException $e) {
            error_log("Error in getUserRating: " . $e->getMessage());
            throw new Exception("사용자의 평점을 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 북마크 추가
     */
    public function addBookmark($resource_id, $user_id, $folder_name = 'default', $note = '') {
        try {
            $sql = "INSERT INTO bookmarks (user_id, resource_id, folder_name, note, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE folder_name = ?, note = ?, updated_at = NOW()";
            
            $this->db->query($sql, [
                $user_id, $resource_id, $folder_name, $note,
                $folder_name, $note
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Error in addBookmark: " . $e->getMessage());
            throw new Exception("북마크를 추가하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 북마크 제거
     */
    public function removeBookmark($resource_id, $user_id) {
        try {
            $sql = "DELETE FROM bookmarks WHERE resource_id = ? AND user_id = ?";
            $this->db->query($sql, [$resource_id, $user_id]);
            return true;
        } catch (PDOException $e) {
            error_log("Error in removeBookmark: " . $e->getMessage());
            throw new Exception("북마크를 제거하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 사용자의 북마크 폴더 목록 조회
     */
    public function getBookmarkFolders($user_id) {
        try {
            $sql = "SELECT DISTINCT folder_name, COUNT(*) as count
                    FROM bookmarks
                    WHERE user_id = ?
                    GROUP BY folder_name
                    ORDER BY folder_name";
            
            return $this->db->fetchAll($sql, [$user_id]);
        } catch (PDOException $e) {
            error_log("Error in getBookmarkFolders: " . $e->getMessage());
            throw new Exception("북마크 폴더를 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 사용자의 북마크된 리소스 목록 조회
     */
    public function getBookmarkedResources($user_id, $folder_name = null) {
        try {
            $sql = "SELECT r.*, b.folder_name, b.note, b.created_at as bookmarked_at
                    FROM {$this->table} r
                    JOIN bookmarks b ON r.id = b.resource_id
                    WHERE b.user_id = ?";
            
            $params = [$user_id];
            
            if ($folder_name !== null) {
                $sql .= " AND b.folder_name = ?";
                $params[] = $folder_name;
            }
            
            $sql .= " ORDER BY b.created_at DESC";
            
            return $this->db->fetchAll($sql, $params);
        } catch (PDOException $e) {
            error_log("Error in getBookmarkedResources: " . $e->getMessage());
            throw new Exception("북마크된 리소스를 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스 공유 링크 생성
     */
    public function createShareLink($resource_id, $user_id, $expires_in = null) {
        try {
            $this->db->beginTransaction();

            // 기존 공유 링크 확인
            $sql = "SELECT share_token FROM resource_shares 
                    WHERE resource_id = ? AND user_id = ? 
                    AND (expires_at IS NULL OR expires_at > NOW())";
            $existing = $this->db->fetch($sql, [$resource_id, $user_id]);

            if ($existing) {
                $this->db->commit();
                return $existing['share_token'];
            }

            // 새로운 공유 링크 생성
            $share_token = bin2hex(random_bytes(16));
            $expires_at = $expires_in ? date('Y-m-d H:i:s', strtotime("+$expires_in")) : null;

            $sql = "INSERT INTO resource_shares (resource_id, user_id, share_token, expires_at) 
                    VALUES (?, ?, ?, ?)";
            
            $this->db->query($sql, [$resource_id, $user_id, $share_token, $expires_at]);

            $this->db->commit();
            return $share_token;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error in createShareLink: " . $e->getMessage());
            throw new Exception("공유 링크를 생성하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 공유 링크로 리소스 조회
     */
    public function getByShareToken($share_token) {
        try {
            $sql = "SELECT r.*, u.name as user_name, 
                    s.created_at as shared_at, s.expires_at,
                    GROUP_CONCAT(t.name) as tags
                    FROM {$this->table} r
                    JOIN resource_shares s ON r.id = s.resource_id
                    LEFT JOIN users u ON r.user_id = u.id
                    LEFT JOIN resource_tags rt ON r.id = rt.resource_id
                    LEFT JOIN tags t ON rt.tag_id = t.id
                    WHERE s.share_token = ?
                    AND (s.expires_at IS NULL OR s.expires_at > NOW())
                    GROUP BY r.id";

            $resource = $this->db->fetch($sql, [$share_token]);

            if ($resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
            }

            return $resource;
        } catch (PDOException $e) {
            error_log("Database error in getByShareToken: " . $e->getMessage());
            throw new Exception("공유된 리소스를 조회하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스가 북마크되었는지 확인
     */
    public function isBookmarked($resource_id, $user_id) {
        try {
            $sql = "SELECT COUNT(*) as count
                    FROM bookmarks
                    WHERE resource_id = ? AND user_id = ?";
            
            $result = $this->db->fetch($sql, [$resource_id, $user_id]);
            return (int)($result['count'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Error in isBookmarked: " . $e->getMessage());
            throw new Exception("북마크 여부를 확인하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 최근 공개 리소스를 가져옵니다.
     *
     * @param int $limit 가져올 리소스의 수
     * @return array 리소스 목록 (태그 포함)
     * @throws ModelException
     */
    public function getRecentPublic($limit = 3, $lang = 'ko')
    {
        $sql = "SELECT r.*, 
                rt.title,
                rt.content,
                rt.description,
                u.name as username,
                r.link as url
                FROM resources r
                JOIN resource_translations rt ON r.id = rt.resource_id AND rt.language_code = ?
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.visibility = 'public'
                AND r.status = 'published'
                AND r.deleted_at IS NULL
                ORDER BY r.created_at DESC
                LIMIT ?";
        
        $resources = $this->db->fetchAll($sql, [$lang, $limit]);
        
        return $resources;
    }

    public function searchWithLang($params) {
        try {
            $lang = Language::getInstance();
            $params['error_messages'] = [
                'db_error' => $lang->get('error.database'),
                'not_found' => $lang->get('error.resource_not_found'),
                'invalid_params' => $lang->get('error.invalid_parameters')
            ];
            return $this->search($params['keyword'], $params['language'], $params['filters']);
        } catch (Exception $e) {
            error_log("Error in searchWithLang: " . $e->getMessage());
            throw new Exception($params['error_messages']['db_error']);
        }
    }

    public function getAll($page = 1, $perPage = 12, $filters = [])
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = [];

        // 검색어 필터
        if (!empty($filters['keyword'])) {
            $where[] = "(title LIKE :keyword OR description LIKE :keyword)";
            $params[':keyword'] = '%' . $filters['keyword'] . '%';
        }

        // 태그 필터
        if (!empty($filters['tag_ids'])) {
            $placeholders = str_repeat('?,', count($filters['tag_ids']) - 1) . '?';
            $where[] = "EXISTS (
                SELECT 1 FROM resource_tags rt 
                WHERE rt.resource_id = r.id 
                AND rt.tag_id IN ($placeholders)
            )";
            $params = array_merge($params, $filters['tag_ids']);
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT r.*, 
                   GROUP_CONCAT(t.id) as tag_ids,
                   GROUP_CONCAT(t.name) as tag_names
            FROM resources r
            LEFT JOIN resource_tags rt ON r.id = rt.resource_id
            LEFT JOIN tags t ON rt.tag_id = t.id
            $whereClause
            GROUP BY r.id
            ORDER BY r.created_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCount($filters = [])
    {
        $params = [];
        $where = [];

        if (!empty($filters['keyword'])) {
            $where[] = "(title LIKE :keyword OR description LIKE :keyword)";
            $params[':keyword'] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['tag_ids'])) {
            $placeholders = str_repeat('?,', count($filters['tag_ids']) - 1) . '?';
            $where[] = "EXISTS (
                SELECT 1 FROM resource_tags rt 
                WHERE rt.resource_id = r.id 
                AND rt.tag_id IN ($placeholders)
            )";
            $params = array_merge($params, $filters['tag_ids']);
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT COUNT(DISTINCT r.id) as total FROM resources r $whereClause";
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public static function getTypes() {
        return [
            'website' => '웹사이트',
            'book' => '책',
            'video' => '비디오',
            'article' => '논문',
            'podcast' => '팟캐스트',
            'experience' => '개인 경험',
            'other' => '기타'
        ];
    }

    /**
     * 리소스 검색 (컨트롤러에서 사용하는 파라미터 일치)
     */
    public function search($params = []) {
        try {
            $where = [];
            $sqlParams = [];
            $limit = isset($params['limit']) ? (int)$params['limit'] : 12;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;

            $language_code = $params['language_code'] ?? 'ko';
            error_log("Searching resources with language: " . $language_code);

            // 기본 WHERE 조건
            $where[] = "r.id IN (SELECT resource_id FROM resource_translations WHERE language_code = ?)";
            $sqlParams[] = $language_code;

            // 키워드 검색
            if (!empty($params['keyword'])) {
                $where[] = "(rt.title LIKE ? OR rt.description LIKE ?)";
                $searchTerm = '%' . $params['keyword'] . '%';
                $sqlParams[] = $searchTerm;
                $sqlParams[] = $searchTerm;
            }

            // 타입 필터
            if (!empty($params['type'])) {
                $where[] = "r.type = ?";
                $sqlParams[] = $params['type'];
            }

            // 공개 여부 필터
            if (isset($params['visibility']) && $params['visibility'] !== '') {
                $where[] = "r.visibility = ?";
                $sqlParams[] = $params['visibility'];
            }

            // 태그 필터
            if (!empty($params['tag_ids'])) {
                $placeholders = str_repeat('?,', count($params['tag_ids']) - 1) . '?';
                $where[] = "EXISTS (SELECT 1 FROM resource_tags rt2 WHERE rt2.resource_id = r.id AND rt2.tag_id IN ($placeholders))";
                $sqlParams = array_merge($sqlParams, $params['tag_ids']);
            }

            // 정렬 조건
            $orderBy = "r.created_at DESC";
            if (!empty($params['sort'])) {
                switch ($params['sort']) {
                    case 'views_desc':
                        $orderBy = "r.view_count DESC";
                        break;
                    case 'rating_desc':
                        $orderBy = "(SELECT AVG(rating) FROM resource_ratings WHERE resource_id = r.id) DESC";
                        break;
                }
            }

            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT r.*, 
                    rt.title,
                    rt.content,
                    rt.description,
                    u.name as author_name,
                    (SELECT AVG(rating) FROM resource_ratings WHERE resource_id = r.id) as rating,
                    GROUP_CONCAT(DISTINCT t.name) as tags
                    FROM resources r
                    JOIN resource_translations rt ON r.id = rt.resource_id AND rt.language_code = ?
                    LEFT JOIN users u ON r.user_id = u.id
                    LEFT JOIN resource_tags rtag ON r.id = rtag.resource_id
                    LEFT JOIN tags t ON rtag.tag_id = t.id
                    $whereClause
                    GROUP BY r.id
                    ORDER BY $orderBy
                    LIMIT ? OFFSET ?";

            $sqlParams = array_merge([$language_code], $sqlParams, [$limit, $offset]);
            
            error_log("Search SQL: " . $sql);
            error_log("Search Params: " . print_r($sqlParams, true));

            $resources = $this->db->fetchAll($sql, $sqlParams);
            
            foreach ($resources as &$resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
            }

            return $resources;
        } catch (Exception $e) {
            error_log("Error in Resource::search: " . $e->getMessage());
            error_log("SQL: " . (isset($sql) ? $sql : 'unset'));
            error_log("Params: " . print_r(isset($sqlParams) ? $sqlParams : [], true));
            throw new Exception("리소스 검색 중 오류가 발생했습니다: " . $e->getMessage());
        }
    }

    /**
     * 리소스 개수 반환 (컨트롤러에서 사용하는 파라미터 일치)
     */
    public function count($params = []) {
        try {
            $where = [];
            $sqlParams = [];

            if (!empty($params['keyword'])) {
                $where[] = "(rt.title LIKE ? OR rt.description LIKE ?)";
                $sqlParams[] = '%' . $params['keyword'] . '%';
                $sqlParams[] = '%' . $params['keyword'] . '%';
            }
            if (!empty($params['type'])) {
                $where[] = "r.type = ?";
                $sqlParams[] = $params['type'];
            }
            if (isset($params['visibility']) && $params['visibility'] !== '') {
                $where[] = "r.visibility = ?";
                $sqlParams[] = $params['visibility'];
            }
            if (!empty($params['tag_ids'])) {
                $placeholders = str_repeat('?,', count($params['tag_ids']) - 1) . '?';
                $where[] = "EXISTS (SELECT 1 FROM resource_tags rt WHERE rt.resource_id = r.id AND rt.tag_id IN ($placeholders))";
                $sqlParams = array_merge($sqlParams, $params['tag_ids']);
            }
            if (!empty($params['language_code'])) {
                $where[] = "rt.language_code = ?";
                $sqlParams[] = $params['language_code'];
            }

            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $sql = "SELECT COUNT(DISTINCT r.id) as total 
                    FROM resources r 
                    JOIN resource_translations rt ON r.id = rt.resource_id 
                    $whereClause";
            
            $result = $this->db->fetch($sql, $sqlParams);
            return $result ? (int)$result['total'] : 0;
        } catch (Exception $e) {
            error_log("Error in Resource::count: " . $e->getMessage());
            error_log("SQL: " . (isset($sql) ? $sql : 'unset'));
            error_log("Params: " . print_r(isset($sqlParams) ? $sqlParams : [], true));
            throw new Exception("리소스 개수 조회 중 오류가 발생했습니다: " . $e->getMessage());
        }
    }

    public function findByUserId($userId, $lang = null) {
        try {
            $lang = $lang ?? $_SESSION['lang'] ?? 'ko';
            $defaultLang = 'en'; // Using 'en' as the default fallback language
            
            $sql = "SELECT r.*, 
                    COALESCE(rt.title, rt_default.title) as title,
                    COALESCE(rt.content, rt_default.content) as content,
                    COALESCE(rt.description, rt_default.description) as description,
                    rt.language_code as translation_language_code,
                    u.name as author_name,
                    GROUP_CONCAT(DISTINCT t.name) as tags,
                    GROUP_CONCAT(DISTINCT t.id) as tag_ids
                FROM resources r
                LEFT JOIN resource_translations rt ON r.id = rt.resource_id AND rt.language_code = ?
                LEFT JOIN resource_translations rt_default ON r.id = rt_default.resource_id AND rt_default.language_code = ?
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN resource_tags rtag ON r.id = rtag.resource_id
                LEFT JOIN tags t ON rtag.tag_id = t.id
                WHERE r.user_id = ? AND r.deleted_at IS NULL
                GROUP BY r.id
                ORDER BY r.created_at DESC";
            
            $resources = $this->db->fetchAll($sql, [$lang, $defaultLang, $userId]);
            
            foreach ($resources as &$resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
                $resource['tag_ids'] = $resource['tag_ids'] ? explode(',', $resource['tag_ids']) : [];
            }
            
            return $resources;
        } catch (\Exception $e) {
            error_log("Error in Resource::findByUserId: " . $e->getMessage());
            throw new \Exception("리소스 조회 중 오류가 발생했습니다.");
        }
    }

    public function findPublicByUserId($userId)
    {
        $sql = "SELECT * FROM resources WHERE user_id = :user_id AND visibility = 'public' ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTotalLikesByUserId($userId)
    {
        $sql = "SELECT SUM(like_count) as total FROM resources WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function getTotalViewsByUserId($userId)
    {
        $sql = "SELECT SUM(view_count) as total FROM resources WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * 사용자의 총 댓글 수 조회
     */
    public function getTotalCommentsByUserId($userId)
    {
        $sql = "SELECT COUNT(*) as total FROM comments WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * 사용자의 최근 활동 조회
     */
    public function getRecentActivityByUserId($userId, $limit = 5)
    {
        $sql = "SELECT 
                    'resource' as type,
                    r.id,
                    rt.title,
                    r.created_at
                FROM resources r
                JOIN resource_translations rt ON r.id = rt.resource_id AND rt.language_code = ?
                WHERE r.user_id = ?
                UNION ALL
                SELECT 
                    'comment' as type,
                    id,
                    content as title,
                    created_at
                FROM comments 
                WHERE user_id = ?
                ORDER BY created_at DESC 
                LIMIT ?";
        
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ko';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lang, $userId, $userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * 사용자의 인기 리소스 조회
     */
    public function getPopularResourcesByUserId($userId, $limit = 3)
    {
        $sql = "SELECT 
                    r.*,
                    COUNT(DISTINCT l.id) as like_count,
                    COUNT(DISTINCT c.id) as comment_count,
                    SUM(r.view_count) as total_views
                FROM resources r
                LEFT JOIN likes l ON r.id = l.resource_id
                LEFT JOIN comments c ON r.id = c.resource_id
                WHERE r.user_id = ?
                GROUP BY r.id
                ORDER BY (COUNT(DISTINCT l.id) + COUNT(DISTINCT c.id) + SUM(r.view_count)) DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * 데이터베이스 인스턴스 반환
     */
    public function getDb() {
        return $this->db;
    }

    /**
     * 특정 언어의 번역본만 삭제
     */
    public function deleteTranslation($resourceId, $languageCode) {
        try {
            $pdo = $this->db->getConnection();
            
            // 이미 진행 중인 트랜잭션이 있다면 롤백
            if (method_exists($pdo, 'inTransaction') && $pdo->inTransaction()) {
                $this->db->rollBack();
            }

            // 새로운 트랜잭션 시작
            $this->db->beginTransaction();

            // 해당 리소스의 번역본 개수 확인
            $sql = "SELECT COUNT(*) as translation_count 
                    FROM resource_translations 
                    WHERE resource_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$resourceId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $translationCount = $result['translation_count'];

            // 번역본이 1개뿐이면 리소스 전체를 삭제
            if ($translationCount <= 1) {
                $this->db->rollBack();
                return $this->delete($resourceId);
            }

            // 특정 언어의 번역본 삭제
            $sql = "DELETE FROM resource_translations 
                    WHERE resource_id = ? AND language_code = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$resourceId, $languageCode]);

            if (!$result) {
                $this->db->rollBack();
                throw new \Exception('번역본 삭제에 실패했습니다.');
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            if (method_exists($pdo, 'inTransaction') && $pdo->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error in deleteTranslation: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 리소스의 번역본 개수를 반환
     */
    public function getTranslationCount($resourceId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM resource_translations WHERE resource_id = ?";
            $result = $this->db->fetch($sql, [$resourceId]);
            return (int)($result['count'] ?? 0);
        } catch (PDOException $e) {
            error_log("Database error in getTranslationCount: " . $e->getMessage());
            throw new Exception("번역본 개수를 조회하는 중 오류가 발생했습니다.");
        }
    }

    public function findByUserIdWithDetails($userId, $lang = null) {
        $lang = $lang ?? $_SESSION['lang'] ?? 'ko';
        $defaultLang = 'en';

        $sql = "SELECT r.*, 
                    COALESCE(rt.title, rt_default.title) as title,
                    COALESCE(rt.content, rt_default.content) as content,
                    COALESCE(rt.description, rt_default.description) as description,
                    rt.language_code as translation_language_code,
                    u.name as author_name,
                    GROUP_CONCAT(DISTINCT t.name) as tags,
                    GROUP_CONCAT(DISTINCT t.id) as tag_ids,
                    (SELECT COUNT(*) FROM likes l WHERE l.resource_id = r.id) as like_count,
                    (SELECT COUNT(*) FROM comments c WHERE c.resource_id = r.id) as comment_count
                FROM resources r
                LEFT JOIN resource_translations rt ON r.id = rt.resource_id AND rt.language_code = ?
                LEFT JOIN resource_translations rt_default ON r.id = rt_default.resource_id AND rt_default.language_code = ?
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN resource_tags rtag ON r.id = rtag.resource_id
                LEFT JOIN tags t ON rtag.tag_id = t.id
                WHERE r.user_id = ? AND r.deleted_at IS NULL
                GROUP BY r.id
                ORDER BY r.created_at DESC";

        $resources = $this->db->fetchAll($sql, [$lang, $defaultLang, $userId]);
        foreach ($resources as &$resource) {
            $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
            $resource['tag_ids'] = $resource['tag_ids'] ? explode(',', $resource['tag_ids']) : [];
        }
        return $resources;
    }

    public function countByUserId($userId) {
        $sql = "SELECT COUNT(*) FROM resources WHERE user_id = :user_id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    public function countPublicByUserId($userId) {
        $sql = "SELECT COUNT(*) FROM resources WHERE user_id = :user_id AND visibility = 'public' AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    public function incrementViewCount($resourceId) {
        error_log("[DEBUG] incrementViewCount SQL 실행: id = $resourceId");
        $sql = "UPDATE resources SET view_count = view_count + 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$resourceId]);
        error_log("[DEBUG] incrementViewCount 쿼리 결과: " . var_export($result, true));
    }

    public function incrementLikeCount($resourceId) {
        $sql = "UPDATE resources SET like_count = like_count + 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$resourceId]);
    }

    public function decrementLikeCount($resourceId) {
        $sql = "UPDATE resources SET like_count = GREATEST(like_count - 1, 0) WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$resourceId]);
    }
}