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
        'title', 'content', 'description', 'file_path', 'slug', 'is_public',
        'link'
    ];

    public function __construct() {
        parent::__construct(Database::getInstance());
    }

    public function getTranslation($id, $language = null)
    {
        if (!$language) {
            $language = Language::getInstance()->getCurrentLanguage();
        }
        $defaultLang = Language::getInstance()->getDefaultLanguage();
        $sql = "SELECT 
                COALESCE(rt.title, rt_default.title) as title,
                COALESCE(rt.content, rt_default.content) as content,
                COALESCE(rt.description, rt_default.description) as description
            FROM resources r
            LEFT JOIN resource_translations rt ON r.id = rt.resource_id AND rt.language_code = ?
            LEFT JOIN resource_translations rt_default ON r.id = rt_default.resource_id AND rt_default.language_code = ?
            WHERE r.id = ?";
        return $this->db->fetch($sql, [$language, $defaultLang, $id]);
    }

    /**
     * 언어별 번역 정보 조인 쿼리 생성 (공통)
     */
    private function translationSelect($alias = 'r', $lang = null, $defaultLang = null) {
        if (!$lang) $lang = Language::getInstance()->getCurrentLanguage();
        if (!$defaultLang) $defaultLang = Language::getInstance()->getDefaultLanguage();
        return [
            "COALESCE(rt.title, rt_default.title) as title",
            "COALESCE(rt.content, rt_default.content) as content",
            "COALESCE(rt.description, rt_default.description) as description",
            "LEFT JOIN resource_translations rt ON {$alias}.id = rt.resource_id AND rt.language_code = ?",
            "LEFT JOIN resource_translations rt_default ON {$alias}.id = rt_default.resource_id AND rt_default.language_code = ?"
        ];
    }

    /**
     * 리소스 목록 (태그 포함, 다국어)
     */
    public function getResourcesWithTags($limit = null, $offset = null, $language = null, $defaultLang = null) {
        try {
            $lang = $language ?: Language::getInstance()->getCurrentLanguage();
            $def = $defaultLang ?: Language::getInstance()->getDefaultLanguage();
            $select = $this->translationSelect('r', $lang, $def);
            $sql = "SELECT r.*, {$select[0]}, {$select[1]}, {$select[2]}, GROUP_CONCAT(t.name) as tags
                    FROM {$this->table} r
                    {$select[3]}
                    {$select[4]}
                    LEFT JOIN resource_tags rtag ON r.id = rtag.resource_id
                    LEFT JOIN tags t ON rtag.tag_id = t.id
                    GROUP BY r.id
                    ORDER BY r.created_at DESC";
            if ($limit !== null) {
                $sql .= " LIMIT ?";
                if ($offset !== null) {
                    $sql .= " OFFSET ?";
                }
            }
            $params = [$lang, $def];
            if ($limit !== null) {
                $params[] = $limit;
                if ($offset !== null) $params[] = $offset;
            }
            $resources = $this->db->fetchAll($sql, $params);
            foreach ($resources as &$resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
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
            $sql = "SELECT r.*, GROUP_CONCAT(t.name) as tags 
                    FROM {$this->table} r 
                    LEFT JOIN resource_tags rt ON r.id = rt.resource_id 
                    LEFT JOIN tags t ON rt.tag_id = t.id 
                    WHERE r.title LIKE ? 
                    OR r.content LIKE ? 
                    GROUP BY r.id 
                    ORDER BY r.created_at DESC";

            if ($limit !== null) {
                $sql .= " LIMIT ?";
                if ($offset !== null) {
                    $sql .= " OFFSET ?";
                }
            }

            $searchTerm = '%' . $query . '%';
            $params = [$searchTerm, $searchTerm];
            
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
            $lang = $language ?: Language::getInstance()->getCurrentLanguage();
            $def = Language::getInstance()->getDefaultLanguage();
            $select = $this->translationSelect('r', $lang, $def);
            $params = [$lang, $def];
            $where = ["rt.language_code = ?"];
            if ($keyword) {
                $where[] = "MATCH(rt.title, rt.content, rt.description) AGAINST (? IN BOOLEAN MODE)";
                $params[] = $keyword;
            }
            if (!empty($filters['tags'])) {
                $placeholders = str_repeat('?,', count($filters['tags']) - 1) . '?';
                $where[] = "EXISTS (SELECT 1 FROM resource_tags rt2 WHERE rt2.resource_id = r.id AND rt2.tag_id IN ($placeholders))";
                $params = array_merge($params, $filters['tags']);
            }
            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $sql = "SELECT r.*, {$select[0]}, {$select[1]}, {$select[2]},
                    GROUP_CONCAT(DISTINCT t.name) as tags,
                    MATCH(rt.title, rt.content, rt.description) AGAINST (? IN BOOLEAN MODE) as relevance
                FROM resources r
                {$select[3]}
                {$select[4]}
                LEFT JOIN resource_tags rtag ON r.id = rtag.resource_id
                LEFT JOIN tags t ON rtag.tag_id = t.id
                $whereClause
                GROUP BY r.id
                ORDER BY relevance DESC, r.created_at DESC";
            if ($keyword) $params[] = $keyword;
            $resources = $this->db->fetchAll($sql, $params);
            foreach ($resources as &$resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
            }
            return $resources;
        } catch (Exception $e) {
            error_log("Error in Resource::searchFulltext: " . $e->getMessage());
            throw new Exception("리소스 검색 중 오류가 발생했습니다.");
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
            $lang = $language ?: Language::getInstance()->getCurrentLanguage();
            $def = $defaultLang ?: Language::getInstance()->getDefaultLanguage();
            $select = $this->translationSelect('r', $lang, $def);
            $sql = "SELECT r.*, rt.language_code as translation_language_code, {$select[0]}, {$select[1]}, {$select[2]},
                    GROUP_CONCAT(DISTINCT t.name) as tags,
                    GROUP_CONCAT(DISTINCT t.id) as tag_ids
                FROM resources r
                {$select[3]}
                {$select[4]}
                LEFT JOIN resource_tags rtag ON r.id = rtag.resource_id
                LEFT JOIN tags t ON rtag.tag_id = t.id
                WHERE r.id = ?
                GROUP BY r.id";
            $resource = $this->db->fetch($sql, [$lang, $def, $id]);
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
     * 태그 이름으로 태그 찾기
     */
    public function findTagByName($name) {
        try {
            $sql = "SELECT * FROM tags WHERE name = ?";
            return $this->db->fetch($sql, [$name]);
        } catch (PDOException $e) {
            error_log("Database error in findTagByName: " . $e->getMessage());
            throw new Exception("태그를 찾는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 새 태그 생성
     */
    public function createTag($name) {
        try {
            $sql = "INSERT INTO tags (name) VALUES (?)";
            $this->db->query($sql, [$name]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Database error in createTag: " . $e->getMessage());
            throw new Exception("태그를 생성하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스에 태그 추가
     */
    public function addTag($resource_id, $tag_name) {
        try {
            $this->db->beginTransaction();

            // 태그가 없으면 생성
            $tag = $this->findTagByName($tag_name);
            if (!$tag) {
                $tag_id = $this->createTag($tag_name);
            } else {
                $tag_id = $tag['id'];
            }

            // 리소스에 태그 추가
            $sql = "INSERT IGNORE INTO resource_tags (resource_id, tag_id) VALUES (?, ?)";
            $this->db->query($sql, [$resource_id, $tag_id]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("Database error in addTag: " . $e->getMessage());
            throw new Exception("태그를 추가하는 중 오류가 발생했습니다.");
        }
    }

    /**
     * 리소스에 기존 태그 추가
     */
    public function addExistingTag($resource_id, $tag_id) {
        try {
            $sql = "INSERT IGNORE INTO resource_tags (resource_id, tag_id) VALUES (?, ?)";
            $this->db->query($sql, [$resource_id, $tag_id]);
            return true;
        } catch (PDOException $e) {
            error_log("Database error in addExistingTag: " . $e->getMessage());
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
     * 리소스 생성
     */
    public function create(array $data): ?int
    {
        try {
            $this->db->beginTransaction();

            // 리소스 기본 정보 저장
            $publishedAt = (isset($data['status']) && $data['status'] === 'published') ? date('Y-m-d H:i:s') : null;
            $sql = "INSERT INTO resources (
                user_id, title, content, description, file_path, visibility, status, slug, is_public, published_at, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $params = [
                $data['user_id'],
                $data['title'],
                $data['content'],
                $data['description'],
                $data['file_path'],
                $data['visibility'] ?? 'public',
                $data['status'] ?? 'draft',
                $data['slug'],
                isset($data['is_public']) ? (int)$data['is_public'] : 0,
                $publishedAt
            ];

            error_log('[DEBUG] Resource INSERT SQL: ' . $sql);
            error_log('[DEBUG] Resource INSERT Params: ' . json_encode($params));
            $this->db->query($sql, $params);
            $errorInfo = $this->db->getConnection()->errorInfo();
            error_log('[DEBUG] Resource INSERT errorInfo: ' . json_encode($errorInfo));

            // 태그 처리
            if (!empty($data['tags'])) {
                foreach ($data['tags'] as $tagName) {
                    try {
                        $tagSql = "INSERT IGNORE INTO tags (name, created_at) VALUES (?, NOW())";
                        $this->db->query($tagSql, [$tagName]);
                        $tagIdSql = "SELECT id FROM tags WHERE name = ?";
                        $tagId = $this->db->query($tagIdSql, [$tagName])->fetch()['id'];
                        $relationSql = "INSERT INTO resource_tags (resource_id, tag_id) VALUES (?, ?)";
                        $this->db->query($relationSql, [$this->db->lastInsertId(), $tagId]);
                    } catch (\Exception $e) {
                        error_log("Tag insert error: " . $e->getMessage());
                        // 태그 하나 실패해도 전체 트랜잭션은 유지
                    }
                }
            }

            $this->db->commit();
            $resourceId = $this->db->lastInsertId();
            if (!$resourceId) {
                // 실제로 데이터가 들어갔는지 slug로 재조회
                $checkSql = "SELECT id FROM resources WHERE slug = ?";
                $row = $this->db->query($checkSql, [$data['slug']])->fetch();
                if ($row && isset($row['id'])) {
                    $resourceId = $row['id'];
                }
            }
            if (!$resourceId) {
                error_log('[ERROR] Resource insert failed. Params: ' . json_encode($params));
                throw new \Exception('리소스 DB 저장 실패');
            }
            return (int)$resourceId;
        } catch (\Exception $e) {
            $pdo = $this->db->getConnection();
            if (method_exists($pdo, 'inTransaction') && $pdo->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error in Resource::create: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 리소스 수정 (다국어)
     */
    public function update($id, array $data): ?array {
        try {
            $this->db->beginTransaction();
            // 기본 정보 업데이트 (title, content, description 제거)
            $updateFields = [];
            $params = [];
            foreach (
                $this->fillable as $field
            ) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
            // status 변경에 따라 published_at 처리
            $currentStatus = null;
            $stmt = $this->db->prepare("SELECT status FROM resources WHERE id = ?");
            $stmt->execute([$id]);
            if ($row = $stmt->fetch()) {
                $currentStatus = $row['status'];
            }
            if (isset($data['status'])) {
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
                    $sql = "INSERT INTO resource_translations (resource_id, language_code, title, content, description) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content), description = VALUES(description)";
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
                $this->db->query("DELETE FROM resource_tags WHERE resource_id = ?", [$id]);
                foreach ($data['tags'] as $tagId) {
                    $sql = "INSERT INTO resource_tags (resource_id, tag_id) VALUES (?, ?)";
                    $this->db->query($sql, [$id, $tagId]);
                }
                $this->db->query("UPDATE tags t SET count = (SELECT COUNT(*) FROM resource_tags rt WHERE rt.tag_id = t.id)");
            }
            $this->db->commit();
            return $this->findById($id);
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error in Resource::update: " . $e->getMessage());
            throw new Exception("리소스 업데이트 중 오류가 발생했습니다.");
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
            $lang = $language ?: Language::getInstance()->getCurrentLanguage();
            $def = $defaultLang ?: Language::getInstance()->getDefaultLanguage();
            $select = $this->translationSelect('r', $lang, $def);
            $sql = "SELECT r.*, {$select[0]}, {$select[1]}, {$select[2]}, GROUP_CONCAT(t.name) as tags FROM {$this->table} r {$select[3]} {$select[4]} LEFT JOIN resource_tags rtag ON r.id = rtag.resource_id LEFT JOIN tags t ON rtag.tag_id = t.id GROUP BY r.id ORDER BY r.created_at DESC";
            $resources = $this->db->fetchAll($sql, [$lang, $def]);
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
     * 리소스의 태그 업데이트
     */
    public function updateResourceTags($resource_id, array $tag_ids) {
        try {
            $this->db->beginTransaction();

            // 기존 태그 삭제
            $sql = "DELETE FROM resource_tags WHERE resource_id = ?";
            $this->db->query($sql, [$resource_id]);

            // 새로운 태그 추가
            if (!empty($tag_ids)) {
                $values = [];
                $params = [];
                foreach ($tag_ids as $tag_id) {
                    $values[] = "(?, ?)";
                    $params[] = $resource_id;
                    $params[] = $tag_id;
                }
                
                $sql = "INSERT INTO resource_tags (resource_id, tag_id) VALUES " . implode(', ', $values);
                $this->db->query($sql, $params);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("Error in updateResourceTags: " . $e->getMessage());
            throw new Exception("리소스의 태그를 업데이트하는 중 오류가 발생했습니다.");
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
    public function getRecentPublic($limit = 10, $language = null, $defaultLang = null)
    {
        try {
            $lang = $language ?: (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ko');
            $def = $defaultLang ?: 'ko';
            $select = $this->translationSelect('r', $lang, $def);
            $sql = "SELECT r.*, {$select[0]}, {$select[1]}, {$select[2]}, u.name as user_name
                    FROM resources r
                    {$select[3]}
                    {$select[4]}
                    LEFT JOIN users u ON r.user_id = u.id
                    WHERE r.visibility = 'public' AND rt.language_code = ?
                    ORDER BY r.created_at DESC
                    LIMIT ?";
            return $this->db->fetchAll($sql, [$lang, $def, $lang, $limit]);
        } catch (PDOException $e) {
            error_log("Database error in getRecentPublic: " . $e->getMessage());
            throw new Exception("최근 공개 리소스를 조회하는 중 오류가 발생했습니다.");
        }
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

            $joinLang = $params['language_code'] ?? 'en';
            $defaultLang = 'ko';
            $whereParams = [];
            if (!empty($params['keyword'])) {
                $where[] = "(COALESCE(rt.title, rt_default.title) LIKE ? OR COALESCE(rt.description, rt_default.description) LIKE ?)";
                $whereParams[] = '%' . $params['keyword'] . '%';
                $whereParams[] = '%' . $params['keyword'] . '%';
            }
            if (!empty($params['type'])) {
                $where[] = "r.type = ?";
                $whereParams[] = $params['type'];
            }
            if (isset($params['is_public']) && $params['is_public'] !== '') {
                $where[] = "r.visibility = ?";
                $whereParams[] = $params['is_public'] == '1' ? 'public' : 'private';
            }
            if (!empty($params['tag_ids'])) {
                $placeholders = str_repeat('?,', count($params['tag_ids']) - 1) . '?';
                $where[] = "EXISTS (SELECT 1 FROM resource_tags rt2 WHERE rt2.resource_id = r.id AND rt2.tag_id IN ($placeholders))";
                $whereParams = array_merge($whereParams, $params['tag_ids']);
            }
            if (!empty($params['language_code'])) {
                $where[] = "EXISTS (SELECT 1 FROM resource_translations rt2 WHERE rt2.resource_id = r.id AND rt2.language_code = ?)";
                $whereParams[] = $params['language_code'];
            }
            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $sql = "SELECT r.*, 
                    COALESCE(rt.title, rt_default.title) as title,
                    COALESCE(rt.content, rt_default.content) as content,
                    COALESCE(rt.description, rt_default.description) as description,
                    u.name as author_name,
                    (SELECT AVG(rating) FROM resource_ratings WHERE resource_id = r.id) as rating
                    FROM resources r
                    LEFT JOIN resource_translations rt ON r.id = rt.resource_id AND rt.language_code = ?
                    LEFT JOIN resource_translations rt_default ON r.id = rt_default.resource_id AND rt_default.language_code = ?
                    LEFT JOIN users u ON r.user_id = u.id
                    $whereClause
                    ORDER BY r.created_at DESC
                    LIMIT ? OFFSET ?";
            $sqlParams = array_merge([$joinLang, $defaultLang], $whereParams, [$limit, $offset]);
            $resources = $this->db->fetchAll($sql, $sqlParams);
            foreach ($resources as &$resource) {
                $resource['tags'] = $resource['tags'] ? explode(',', $resource['tags']) : [];
            }
            return $resources;
        } catch (Exception $e) {
            error_log("Error in Resource::search: " . $e->getMessage());
            error_log("SQL: " . (isset($sql) ? $sql : 'unset'));
            error_log("Params: " . print_r(isset($sqlParams) ? $sqlParams : [], true));
            echo '<pre style="color:red;font-weight:bold;">MODEL ERROR: ';
            var_dump($e);
            echo "\nSQL: ", isset($sql) ? $sql : 'unset', "\nParams: ", print_r(isset($sqlParams) ? $sqlParams : [], true), "</pre>";
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
                $where[] = "(r.title LIKE ? OR r.description LIKE ?)";
                $sqlParams[] = '%' . $params['keyword'] . '%';
                $sqlParams[] = '%' . $params['keyword'] . '%';
            }
            if (!empty($params['type'])) {
                $where[] = "r.type = ?";
                $sqlParams[] = $params['type'];
            }
            if (isset($params['is_public']) && $params['is_public'] !== '') {
                $where[] = "r.visibility = ?";
                $sqlParams[] = $params['is_public'] == '1' ? 'public' : 'private';
            }
            if (!empty($params['tag_ids'])) {
                $placeholders = str_repeat('?,', count($params['tag_ids']) - 1) . '?';
                $where[] = "EXISTS (SELECT 1 FROM resource_tags rt WHERE rt.resource_id = r.id AND rt.tag_id IN ($placeholders))";
                $sqlParams = array_merge($sqlParams, $params['tag_ids']);
            }
            if (!empty($params['language_code'])) {
                $where[] = "EXISTS (SELECT 1 FROM resource_translations rt WHERE rt.resource_id = r.id AND rt.language_code = ?)";
                $sqlParams[] = $params['language_code'];
            }

            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $sql = "SELECT COUNT(DISTINCT r.id) as total FROM resources r $whereClause";
            
            $result = $this->db->fetch($sql, $sqlParams);
            return $result ? (int)$result['total'] : 0;
        } catch (Exception $e) {
            error_log("Error in Resource::count: " . $e->getMessage());
            error_log("SQL: " . (isset($sql) ? $sql : 'unset'));
            error_log("Params: " . print_r(isset($sqlParams) ? $sqlParams : [], true));
            echo '<pre style="color:red;font-weight:bold;">MODEL ERROR: ';
            var_dump($e);
            echo "\nSQL: ", isset($sql) ? $sql : 'unset', "\nParams: ", print_r(isset($sqlParams) ? $sqlParams : [], true), "</pre>";
            throw new Exception("리소스 개수 조회 중 오류가 발생했습니다: " . $e->getMessage());
        }
    }

    public function findByUserId($userId)
    {
        $sql = "SELECT * FROM resources WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findPublicByUserId($userId)
    {
        $sql = "SELECT * FROM resources WHERE user_id = :user_id AND is_public = 1 ORDER BY created_at DESC";
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
                    id,
                    title,
                    created_at
                FROM resources 
                WHERE user_id = ?
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
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $userId, $limit]);
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
}
