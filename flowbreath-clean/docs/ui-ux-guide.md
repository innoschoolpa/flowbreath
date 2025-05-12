# SQL Resource Management System - UI/UX 가이드

## 디자인 원칙

### 1. 일관성
- 색상 팔레트
  ```css
  :root {
    --primary-color: #2563eb;
    --secondary-color: #475569;
    --success-color: #22c55e;
    --danger-color: #ef4444;
    --warning-color: #f59e0b;
    --info-color: #3b82f6;
    --background-color: #f8fafc;
    --text-color: #1e293b;
  }
  ```

- 타이포그래피
  ```css
  :root {
    --font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    --heading-1: 2.5rem;
    --heading-2: 2rem;
    --heading-3: 1.75rem;
    --body-text: 1rem;
    --small-text: 0.875rem;
  }
  ```

- 간격 시스템
  ```css
  :root {
    --spacing-xs: 0.25rem;
    --spacing-sm: 0.5rem;
    --spacing-md: 1rem;
    --spacing-lg: 1.5rem;
    --spacing-xl: 2rem;
  }
  ```

### 2. 접근성
- 색상 대비 (WCAG 2.1 준수)
- 키보드 네비게이션
- 스크린 리더 지원
- 반응형 디자인

### 3. 사용성
- 직관적인 네비게이션
- 명확한 피드백
- 효율적인 작업 흐름
- 오류 방지 및 복구

## 컴포넌트 디자인

### 1. 버튼
```css
.button {
  padding: var(--spacing-sm) var(--spacing-md);
  border-radius: 0.375rem;
  font-weight: 500;
  transition: all 0.2s;
}

.button-primary {
  background-color: var(--primary-color);
  color: white;
}

.button-secondary {
  background-color: var(--secondary-color);
  color: white;
}

.button-danger {
  background-color: var(--danger-color);
  color: white;
}
```

### 2. 입력 필드
```css
.input {
  padding: var(--spacing-sm);
  border: 1px solid #e2e8f0;
  border-radius: 0.375rem;
  width: 100%;
  transition: border-color 0.2s;
}

.input:focus {
  border-color: var(--primary-color);
  outline: none;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}
```

### 3. 카드
```css
.card {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: var(--spacing-lg);
}

.card-header {
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: var(--spacing-md);
  margin-bottom: var(--spacing-md);
}
```

### 4. 테이블
```css
.table {
  width: 100%;
  border-collapse: collapse;
}

.table th {
  background-color: #f8fafc;
  padding: var(--spacing-sm);
  text-align: left;
  font-weight: 500;
}

.table td {
  padding: var(--spacing-sm);
  border-bottom: 1px solid #e2e8f0;
}
```

## 페이지 레이아웃

### 1. 대시보드
```html
<div class="dashboard">
  <header class="dashboard-header">
    <h1>SQL Resource Management</h1>
    <div class="user-menu">
      <!-- 사용자 메뉴 -->
    </div>
  </header>
  
  <nav class="sidebar">
    <!-- 네비게이션 메뉴 -->
  </nav>
  
  <main class="content">
    <div class="stats-grid">
      <!-- 통계 카드 -->
    </div>
    
    <div class="recent-resources">
      <!-- 최근 리소스 목록 -->
    </div>
  </main>
</div>
```

### 2. 리소스 목록
```html
<div class="resource-list">
  <div class="resource-filters">
    <!-- 필터 옵션 -->
  </div>
  
  <div class="resource-table">
    <!-- 리소스 테이블 -->
  </div>
  
  <div class="pagination">
    <!-- 페이지네이션 -->
  </div>
</div>
```

### 3. 리소스 상세
```html
<div class="resource-detail">
  <div class="resource-header">
    <!-- 리소스 정보 -->
  </div>
  
  <div class="resource-content">
    <!-- SQL 내용 -->
  </div>
  
  <div class="resource-actions">
    <!-- 작업 버튼 -->
  </div>
</div>
```

## 반응형 디자인

### 1. 브레이크포인트
```css
/* 모바일 */
@media (max-width: 640px) {
  .container {
    padding: var(--spacing-sm);
  }
  
  .grid {
    grid-template-columns: 1fr;
  }
}

/* 태블릿 */
@media (min-width: 641px) and (max-width: 1024px) {
  .container {
    padding: var(--spacing-md);
  }
  
  .grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* 데스크톱 */
@media (min-width: 1025px) {
  .container {
    padding: var(--spacing-lg);
  }
  
  .grid {
    grid-template-columns: repeat(3, 1fr);
  }
}
```

### 2. 모바일 최적화
- 터치 타겟 크기 (최소 44x44px)
- 스와이프 제스처
- 모바일 친화적 메뉴

## 애니메이션 및 전환

### 1. 로딩 상태
```css
.loading {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
```

### 2. 전환 효과
```css
.fade-enter {
  opacity: 0;
}

.fade-enter-active {
  opacity: 1;
  transition: opacity 0.3s;
}

.fade-exit {
  opacity: 1;
}

.fade-exit-active {
  opacity: 0;
  transition: opacity 0.3s;
}
```

## 사용자 피드백

### 1. 알림
```css
.notification {
  position: fixed;
  bottom: var(--spacing-lg);
  right: var(--spacing-lg);
  padding: var(--spacing-md);
  border-radius: 0.375rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
  }
  to {
    transform: translateX(0);
  }
}
```

### 2. 진행 상태
```css
.progress-bar {
  height: 4px;
  background-color: #e2e8f0;
  border-radius: 2px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background-color: var(--primary-color);
  transition: width 0.3s ease-out;
}
```

## 성능 최적화

### 1. 이미지 최적화
- WebP 포맷 사용
- 지연 로딩
- 반응형 이미지

### 2. 코드 최적화
- CSS 미니파이
- JavaScript 번들링
- 트리 쉐이킹

## 테마 지원

### 1. 라이트 모드
```css
:root {
  --background-color: #f8fafc;
  --text-color: #1e293b;
  --border-color: #e2e8f0;
}
```

### 2. 다크 모드
```css
[data-theme="dark"] {
  --background-color: #1e293b;
  --text-color: #f8fafc;
  --border-color: #475569;
}
```

## 접근성 가이드라인

### 1. 키보드 네비게이션
```css
:focus {
  outline: 2px solid var(--primary-color);
  outline-offset: 2px;
}

:focus:not(:focus-visible) {
  outline: none;
}
```

### 2. 스크린 리더
```html
<button aria-label="SQL 파일 업로드">
  <svg aria-hidden="true">
    <!-- 아이콘 -->
  </svg>
</button>
```

## 사용자 테스트

### 1. 테스트 시나리오
- 파일 업로드 프로세스
- 리소스 검색 및 필터링
- SQL 실행 및 모니터링
- 오류 처리 및 복구

### 2. 피드백 수집
- 사용자 설문
- 행동 분석
- 오류 보고
- 기능 요청 