<?php
// 캐싱 비활성화 (테스트용)
ini_set('opcache.enable', 0);

// 세션 시작 (CSRF 토큰용)
session_start();

// CSRF 토큰 생성
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// 데이터베이스 연결
$databasePath = __DIR__ . '/config/database.php';
if (!file_exists($databasePath)) {
    error_log("Database configuration file not found at: $databasePath");
    die("Error: Database configuration file not found at: $databasePath");
}
require_once $databasePath;
error_log("Database.php required successfully in index.php");

// Database 클래스 존재 확인
if (!class_exists('Database')) {
    error_log("Class Database not found after requiring database.php");
    die("Error: Database class not found after requiring database.php");
}
error_log("Class Database found successfully");

try {
    $pdo = Database::getConnection();
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Error: Unable to connect to database. Please try again later.");
}

// 사연 저장 및 삭제
$delete_password = 'delete123'; // 간단한 삭제 비밀번호 설정
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF 토큰 검증
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        error_log("CSRF token validation failed");
        die("Error: Invalid request.");
    }

    if (isset($_POST['story'])) {
        $story = htmlspecialchars(trim($_POST['story']));
        if (!empty($story)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO stories (story_text) VALUES (?)");
                $stmt->execute([$story]);
                header("Location: ?lang=$lang#community");
                exit;
            } catch (PDOException $e) {
                error_log("Failed to save story: " . $e->getMessage());
            }
        }
    } elseif (isset($_POST['delete_story']) && isset($_POST['story_id']) && isset($_POST['delete_password'])) {
        if ($_POST['delete_password'] !== $delete_password) {
            error_log("Invalid delete password for story ID: " . $_POST['story_id']);
            $delete_error = ($lang === 'ko' ? '삭제 비밀번호가 틀렸습니다.' : 'Invalid delete password.');
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM stories WHERE id = ?");
                $stmt->execute([$_POST['story_id']]);
                header("Location: ?lang=$lang#community");
                exit;
            } catch (PDOException $e) {
                error_log("Failed to delete story: " . $e->getMessage());
            }
        }
    }
}

// 저장된 이야기 목록 가져오기 (페이지네이션 준비)
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM stories");
    $total_stories = $stmt->fetchColumn();
    $total_pages = ceil($total_stories / $per_page);

    $stmt = $pdo->prepare("SELECT * FROM stories ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$per_page, $offset]);
    $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Failed to fetch stories: " . $e->getMessage());
    $stories = [];
}

// 언어 설정
$lang = isset($_GET['lang']) && $_GET['lang'] === 'en' ? 'en' : 'ko';

// 콘텐츠 배열 (한국어/영어)
$content = [
  'ko' => [
    'title' => 'FlowBreath - 복식 호흡으로 흐름을',
    'meta_description' => '복식 호흡으로 감정을 가라앉히고, 노래를 더 멋지게 부르며, 기의 흐름을 느껴보세요. FlowBreath와 함께 쉽게 배우세요.',
    'hero' => [
      'heading' => '복식 호흡으로 삶에 흐름을',
      'subheading' => '화날 때 평온을, 노래에 감정을, 몸과 마음에 공명을',
      'cta' => '지금 시작하기'
    ],
    'about' => [
      'heading' => '복식 호흡이란?',
      'text' => '복식 호흡은 배를 부풀리며 깊게 숨쉬는 방식입니다. 화가 날 때 마음을 진정시키고, 노래를 더 잘 부르며, 강물처럼 흐르는 기를 깨웁니다. FlowBreath는 누구나 쉽게 배울 수 있도록 돕습니다. 저는 클라리넷 연주로 복식 호흡을 익히며 기의 흐름을 발견했어요.'
    ],
    'benefits' => [
      'heading' => '복식 호흡의 이점',
      'items' => [
        ['title' => '감정 조절', 'desc' => '화가 나면 배로 3번 숨쉬어 평온을 찾아요.'],
        ['title' => '노래 실력 향상', 'desc' => '배로 숨쉬며 음정과 감정을 더 생생히 표현하세요.'],
        ['title' => '심신 건강', 'desc' => '폐 건강, 소화, 집중력을 키워요.'],
        ['title' => '기의 흐름', 'desc' => '복식 호흡으로 몸과 마음의 공명을 느껴보세요.']
      ]
    ],
    'learn' => [
      'heading' => '복식 호흡 배우기',
      'guide' => [
        'title' => '복식 호흡 5단계',
        'steps' => [
          '편안히 누워 배에 손을 올립니다.',
          '코로 들이마시며 배를 부풀립니다.',
          '입으로 천천히 내쉬며 배를 꺼지게 합니다.',
          '5분 동안 가슴 움직임을 최소화하며 연습합니다.',
          '서거나 걸으며 일상에 적용합니다.'
        ]
      ],
      'videos' => [
        [
          'title' => '복식 호흡의 진실',
          'url' => 'https://www.youtube.com/embed/kvv-T4VGKok', // 5-Minute Belly Breathing
          'desc' => '배로 숨쉬는 법을 쉽게 배워보세요.'
        ],
        [
          'title' => '5분 초보자 복식 호흡',
          'url' => 'https://www.youtube.com/embed/8-_NNCrrdus', // 5-Minute Belly Breathing
          'desc' => '배로 숨쉬는 법을 쉽게 배워보세요.'
        ],
        [
          'title' => '분노 완화 심호흡',
          'url' => 'https://www.youtube.com/embed/jCSxggIjVwU', // Breathing for Stress Relief
          'desc' => '화날 때 마음을 진정시키는 호흡법.'
        ],
        [
          'title' => '노래를 위한 복식 호흡',
          'url' => 'https://www.youtube.com/embed/wBPnBndBS4A', // Breathing for Singing
          'desc' => '노래 실력을 키우는 호흡 연습.'
        ]
      ],
      'kids' => [
        'title' => '어린이 배 풍선 놀이',
        'desc' => '배를 풍선처럼 부풀리며 즐겁게 숨쉬기! 숨을 들이마실 때 배를 크게 부풀리고, 내쉴 때 배를 꺼뜨려 보세요. 5일 챌린지로 노래도 더 잘 불러요.',
        'gif_url' => 'https://media.giphy.com/media/26uf2YTgmlP4kAaaY/giphy.gif' // 풍선 부풀리는 GIF
      ]
    ],
    'community' => [
      'heading' => '흐름을 함께',
      'text' => '복식 호흡으로 바뀐 삶의 이야기를 공유하세요. 가족, 학생, 친구들과 함께 공명해보세요!',
      'cta' => '커뮤니티 가입',
      'form' => [
        'label' => '당신의 이야기를 들려주세요',
        'placeholder' => '복식 호흡으로 어떤 변화를 느꼈나요?',
        'submit' => '공유하기'
      ],
      'stories_heading' => '공유된 이야기',
      'no_stories' => '아직 공유된 이야기가 없습니다.',
      'delete_confirm' => '이 이야기를 삭제하시겠습니까?',
      'delete_password_label' => '삭제 비밀번호',
      'delete_password_placeholder' => '비밀번호를 입력하세요',
      'delete_error' => '삭제 비밀번호가 틀렸습니다.',
      'more_stories' => '더 보기'
    ],
    'footer' => [
      'copyright' => '© 2025 FlowBreath. 모든 권리 보유.',
      'contact' => '연락처: info@flowbreath.io'
    ]
  ],
  'en' => [
    'title' => 'FlowBreath - Find Your Flow with Breathing',
    'meta_description' => 'Discover diaphragmatic breathing to calm emotions, enhance singing, and feel energy flow. Learn easily with FlowBreath.',
    'hero' => [
      'heading' => 'Bring Flow to Your Life with Breathing',
      'subheading' => 'Find calm in anger, passion in singing, and resonance in body and mind',
      'cta' => 'Get Started Now'
    ],
    'about' => [
      'heading' => 'What is Diaphragmatic Breathing?',
      'text' => 'Diaphragmatic breathing, or belly breathing, involves deep breaths that expand the belly. It calms anger, enhances singing, and awakens energy flow like a flowing river. FlowBreath makes it easy for everyone to learn. I discovered this through clarinet playing.'
    ],
    'benefits' => [
      'heading' => 'Benefits of Diaphragmatic Breathing',
      'items' => [
        ['title' => 'Emotional Regulation', 'desc' => 'Breathe deeply to find calm during anger or stress.'],
        ['title' => 'Enhanced Singing', 'desc' => 'Improve pitch and expression with breath control.'],
        ['title' => 'Mind-Body Health', 'desc' => 'Boost lung health, digestion, and focus.'],
        ['title' => 'Energy Flow', 'desc' => 'Feel the resonance of body and mind with deep breaths.']
      ]
    ],
    'learn' => [
      'heading' => 'Learn Diaphragmatic Breathing',
      'guide' => [
        'title' => '5 Steps to Diaphragmatic Breathing',
        'steps' => [
          'Lie down comfortably and place a hand on your belly.',
          'Inhale through your nose, expanding your belly.',
          'Exhale slowly through your mouth, letting your belly fall.',
          'Practice for 5 minutes, minimizing chest movement.',
          'Apply in daily life while standing or walking.'
        ]
      ],
      'videos' => [
        [
          'title' => '5-Minute Beginner Breathing',
          'url' => 'https://www.youtube.com/embed/8-_NNCrrdus', // 5-Minute Belly Breathing
          'desc' => 'Learn to breathe with your belly easily.'
        ],
        [
          'title' => 'Breathing for Anger Relief',
          'url' => 'https://www.youtube.com/embed/9p3WnsFjBHI', // Breathing for Stress Relief
          'desc' => 'Calm your mind when angry with deep breaths.'
        ],
        [
          'title' => 'Breathing for Singing',
          'url' => 'https://www.youtube.com/embed/0r3bMzyR4xg', // Breathing for Singing
          'desc' => 'Practice breathing to boost your singing skills.'
        ]
      ],
      'kids' => [
        'title' => "Kids' Belly Balloon Play",
        'desc' => "Blow up your belly like a balloon and have fun breathing! Inhale to make your belly big, and exhale to let it shrink. Try our 5-day challenge to sing better.",
        'gif_url' => 'https://media.giphy.com/media/26uf2YTgmlP4kAaaY/giphy.gif' // 풍선 부풀리는 GIF
      ]
    ],
    'community' => [
      'heading' => 'Join the Flow',
      'text' => 'Share stories of how breathing changed your life. Learn with family, students, and friends to resonate together!',
      'cta' => 'Join Community',
      'form' => [
        'label' => 'Share Your Story',
        'placeholder' => 'How has breathing changed your life?',
        'submit' => 'Submit'
      ],
      'stories_heading' => 'Shared Stories',
      'no_stories' => 'No stories shared yet.',
      'delete_confirm' => 'Are you sure you want to delete this story?',
      'delete_password_label' => 'Delete Password',
      'delete_password_placeholder' => 'Enter password',
      'delete_error' => 'Invalid delete password.',
      'more_stories' => 'Load More'
    ],
    'footer' => [
      'copyright' => '© 2025 FlowBreath. All rights reserved.',
      'contact' => 'Contact: info@flowbreath.io'
    ]
  ]
];
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?php echo $content[$lang]['meta_description']; ?>">
  <meta name="keywords" content="복식 호흡, diaphragmatic breathing, belly breathing, 감정 조절, 노래, 기의 흐름, FlowBreath">
  <meta name="author" content="FlowBreath">
  <title><?php echo $content[$lang]['title']; ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: 'Noto Sans KR', Arial, sans-serif; }
    .hero-bg { background: linear-gradient(to bottom, #4A90E2, #50C9C3); }
    .video-container { max-width: 800px; margin: 0 auto; }
    .lang-toggle { position: fixed; top: 10px; right: 10px; z-index: 50; }
    .section-padding { padding: 4rem 1rem; }
    .benefit-card { transition: transform 0.3s; }
    .benefit-card:hover { transform: translateY(-5px); }
    .video-frame { border-radius: 8px; }
    .scroll-down { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); animation: bounce 2s infinite; }
    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
      40% { transform: translateX(-50%) translateY(-10px); }
      60% { transform: translateX(-50%) translateY(-5px); }
    }
    .benefit-icon { width: 40px; height: 40px; margin: 0 auto 10px; }
    .gif-image { width: 200px; height: auto; margin: 10px auto; display: block; }
    .story-card { transition: all 0.3s ease; border: 1px solid #e5e7eb; }
    .story-card:hover { box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
    .delete-btn:hover { color: #ef4444; }
  </style>
</head>
<body class="bg-gray-100">
  <!-- Language Toggle -->
  <a href="?lang=<?php echo $lang === 'ko' ? 'en' : 'ko'; ?>" class="lang-toggle bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600" aria-label="Switch language">
    <?php echo $lang === 'ko' ? 'ENG' : '한국어'; ?>
  </a>

  <!-- Hero Section -->
  <section class="hero-bg text-white text-center section-padding relative" role="banner">
    <h1 class="text-4xl md:text-5xl font-bold mb-4"><?php echo $content[$lang]['hero']['heading']; ?></h1>
    <p class="text-xl md:text-2xl mb-6"><?php echo $content[$lang]['hero']['subheading']; ?></p>
    <a href="#learn" class="bg-white text-blue-500 px-6 py-3 rounded-full font-semibold hover:bg-gray-100" aria-label="Start learning diaphragmatic breathing">
      <?php echo $content[$lang]['hero']['cta']; ?>
    </a>
    <div class="scroll-down">
      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
      </svg>
    </div>
  </section>

  <!-- About Section -->
  <section class="section-padding bg-white text-center" role="region" aria-labelledby="about-heading">
    <h2 id="about-heading" class="text-3xl font-bold mb-6"><?php echo $content[$lang]['about']['heading']; ?></h2>
    <p class="max-w-3xl mx-auto text-lg leading-relaxed"><?php echo $content[$lang]['about']['text']; ?></p>
  </section>

  <!-- Benefits Section -->
  <section class="section-padding bg-gray-100" role="region" aria-labelledby="benefits-heading">
    <h2 id="benefits-heading" class="text-3xl font-bold text-center mb-8"><?php echo $content[$lang]['benefits']['heading']; ?></h2>
    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php foreach ($content[$lang]['benefits']['items'] as $index => $item): ?>
        <div class="benefit-card bg-white p-6 rounded-lg shadow-md text-center" role="article">
          <svg class="benefit-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <?php if ($index == 0): // 감정 조절 ?>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            <?php elseif ($index == 1): // 노래 실력 ?>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
            <?php elseif ($index == 2): // 심신 건강 ?>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
            <?php else: // 기의 흐름 ?>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16M4 12l4-4m-4 4l4 4"></path>
            <?php endif; ?>
          </svg>
          <h3 class="text-xl font-semibold mb-2"><?php echo $item['title']; ?></h3>
          <p class="text-gray-600"><?php echo $item['desc']; ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Learn Section -->
  <section id="learn" class="section-padding bg-white" role="region" aria-labelledby="learn-heading">
    <h2 id="learn-heading" class="text-3xl font-bold text-center mb-8"><?php echo $content[$lang]['learn']['heading']; ?></h2>
    <div class="video-container">
      <!-- Step-by-Step Guide -->
      <div class="mb-12">
        <h3 class="text-xl font-semibold mb-4"><?php echo $content[$lang]['learn']['guide']['title']; ?></h3>
        <ol class="list-decimal text-left max-w-3xl mx-auto pl-6">
          <?php foreach ($content[$lang]['learn']['guide']['steps'] as $step): ?>
            <li class="mb-2"><?php echo $step; ?></li>
          <?php endforeach; ?>
        </ol>
      </div>
      <!-- Kids Section -->
      <div class="mb-12">
        <h3 class="text-xl font-semibold mb-4"><?php echo $content[$lang]['learn']['kids']['title']; ?></h3>
        <p class="text-gray-600 mb-4"><?php echo $content[$lang]['learn']['kids']['desc']; ?></p>
        <img src="<?php echo $content[$lang]['learn']['kids']['gif_url']; ?>" alt="Belly breathing animation" class="gif-image">
      </div>
      <!-- Videos -->
      <?php foreach ($content[$lang]['learn']['videos'] as $video): ?>
        <div class="mb-12">
          <h3 class="text-xl font-semibold mb-2"><?php echo $video['title']; ?></h3>
          <p class="text-gray-600 mb-4"><?php echo $video['desc']; ?></p>
          <iframe class="video-frame" width="100%" height="450" src="<?php echo $video['url']; ?>" frameborder="0" allowfullscreen aria-label="<?php echo $video['title']; ?> video"></iframe>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Community Section -->
  <section id="community" class="section-padding bg-white" role="region" aria-labelledby="community-heading">
    <h2 id="community-heading" class="text-3xl font-bold text-center mb-8"><?php echo $content[$lang]['community']['heading']; ?></h2>
    <p class="max-w-3xl mx-auto text-center mb-8"><?php echo $content[$lang]['community']['text']; ?></p>
    
    <!-- Story Form -->
    <div class="max-w-2xl mx-auto mb-12">
      <form method="post" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <label for="story" class="block text-lg font-semibold"><?php echo $content[$lang]['community']['form']['label']; ?></label>
        <textarea id="story" name="story" rows="4" class="w-full p-4 border rounded-lg focus:ring-2 focus:ring-blue-500" 
                  placeholder="<?php echo $content[$lang]['community']['form']['placeholder']; ?>" required></textarea>
        <button type="submit" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600">
          <?php echo $content[$lang]['community']['form']['submit']; ?>
        </button>
      </form>
    </div>

    <!-- Stories List -->
    <div class="max-w-4xl mx-auto">
      <h3 class="text-2xl font-bold mb-6 text-center"><?php echo $content[$lang]['community']['stories_heading']; ?></h3>
      <?php if (isset($delete_error)): ?>
        <p class="text-red-500 text-center mb-4"><?php echo $content[$lang]['community']['delete_error']; ?></p>
      <?php endif; ?>
      <?php if (!empty($stories)): ?>
        <div class="space-y-6">
          <?php foreach ($stories as $story): ?>
            <div class="story-card bg-gray-50 p-6 rounded-lg relative">
              <p class="text-gray-700 mb-2"><?php echo nl2br($story['story_text']); ?></p>
              <div class="flex justify-between items-center">
                <div class="text-sm text-gray-500">
                  <?php 
                    $date = new DateTime($story['created_at']);
                    echo $date->format($lang === 'ko' ? 'Y년 m월 d일 H:i (KST)' : 'F j, Y g:i A (KST)');
                  ?>
                </div>
                <div class="flex items-center space-x-2">
                  <form method="post" class="inline" onsubmit="return confirm('<?php echo $content[$lang]['community']['delete_confirm']; ?>');">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="story_id" value="<?php echo $story['id']; ?>">
                    <input type="hidden" name="delete_story" value="1">
                    <input type="password" name="delete_password" placeholder="<?php echo $content[$lang]['community']['delete_password_placeholder']; ?>" 
                           class="border rounded px-2 py-1 text-sm mr-2" required>
                    <button type="submit" class="delete-btn text-red-500 hover:text-red-700">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                      </svg>
                    </button>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
          <div class="mt-8 flex justify-center">
            <?php if ($page < $total_pages): ?>
              <a href="?lang=<?php echo $lang; ?>&page=<?php echo $page + 1; ?>#community" 
                 class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600">
                <?php echo $content[$lang]['community']['more_stories']; ?>
              </a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <p class="text-center text-gray-500"><?php echo $content[$lang]['community']['no_stories']; ?></p>
      <?php endif; ?>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-gray-800 text-white py-6 text-center" role="contentinfo">
    <p><?php echo $content[$lang]['footer']['copyright']; ?></p>
    <p><?php echo $content[$lang]['footer']['contact']; ?></p>
  </footer>
</body>
</html>