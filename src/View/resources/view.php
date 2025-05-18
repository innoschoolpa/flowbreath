<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($resource['title']) ?></h1>
        <div class="flex space-x-4">
            <button onclick="toggleBookmark()" id="bookmarkButton" class="flex items-center space-x-2 px-4 py-2 rounded-lg <?= $isBookmarked ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <i class="fas fa-bookmark"></i>
                <span id="bookmarkText"><?= $isBookmarked ? 'Bookmarked' : 'Bookmark' ?></span>
            </button>
            <?php if ($isOwner): ?>
                <a href="/resources/edit/<?= $resource['id'] ?>" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                    Edit
                </a>
                <button onclick="deleteResource()" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                    Delete
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <article class="prose prose-lg max-w-none">
            <div class="resource-content">
                <?= $resource['content'] ?>
            </div>
        </article>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Resource Details</h2>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">Author</dt>
                <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($resource['author_name']) ?></dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Created</dt>
                <dd class="mt-1 text-sm text-gray-900"><?= date('F j, Y', strtotime($resource['created_at'])) ?></dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">File Type</dt>
                <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($resource['file_type']) ?></dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                <dd class="mt-1 text-sm text-gray-900"><?= date('F j, Y', strtotime($resource['updated_at'])) ?></dd>
            </div>
        </dl>
    </div>
</div>

<style>
/* Tailwind prose 스타일 오버라이드 */
.prose {
    max-width: 100% !important;
}

.prose img {
    max-width: 100% !important;
    height: auto !important;
    margin: 1rem auto !important;
}

.prose figure {
    max-width: 100% !important;
    margin: 1rem 0 !important;
}

.prose figure img {
    max-width: 100% !important;
    height: auto !important;
}

/* 이미지 스타일 */
.resource-content {
    width: 100%;
    overflow-x: hidden;
}

.resource-content img {
    max-width: 100% !important;
    height: auto !important;
    display: block;
    margin: 1rem auto;
}

.resource-content figure {
    max-width: 100% !important;
    margin: 1rem 0;
}

.resource-content figure img {
    max-width: 100% !important;
    height: auto !important;
}

.resource-content .image-style-block {
    max-width: 100% !important;
    margin: 1rem 0;
}

.resource-content .image-style-inline {
    max-width: 45% !important;
    margin: 0.5rem;
}

.resource-content .image-style-side {
    max-width: 30% !important;
    float: right;
    margin: 0.5rem 0 0.5rem 1rem;
}

.resource-content .image-style-align-left {
    float: left;
    margin: 0.5rem 1rem 0.5rem 0;
    max-width: 45% !important;
}

.resource-content .image-style-align-center {
    margin: 1rem auto;
    max-width: 100% !important;
}

.resource-content .image-style-align-right {
    float: right;
    margin: 0.5rem 0 0.5rem 1rem;
    max-width: 45% !important;
}

@media (max-width: 768px) {
    .resource-content .image-style-side,
    .resource-content .image-style-inline,
    .resource-content .image-style-align-left,
    .resource-content .image-style-align-right {
        max-width: 100% !important;
        float: none;
        margin: 1rem 0;
    }
}
</style>

<script>
async function toggleBookmark() {
    try {
        const button = document.getElementById('bookmarkButton');
        const text = document.getElementById('bookmarkText');
        const isCurrentlyBookmarked = button.classList.contains('bg-blue-500');

        const response = await fetch('/api/bookmarks', {
            method: isCurrentlyBookmarked ? 'DELETE' : 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                resourceId: <?= $resource['id'] ?>
            })
        });

        if (response.ok) {
            if (isCurrentlyBookmarked) {
                button.classList.remove('bg-blue-500', 'text-white');
                button.classList.add('bg-gray-100', 'text-gray-700');
                text.textContent = 'Bookmark';
            } else {
                button.classList.remove('bg-gray-100', 'text-gray-700');
                button.classList.add('bg-blue-500', 'text-white');
                text.textContent = 'Bookmarked';
            }
        } else {
            const data = await response.json();
            alert(data.message || 'Failed to update bookmark');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to update bookmark');
    }
}

async function deleteResource() {
    if (!confirm('Are you sure you want to delete this resource?')) {
        return;
    }

    try {
        const response = await fetch('/api/resources/<?= $resource['id'] ?>', {
            method: 'DELETE'
        });

        if (response.ok) {
            window.location.href = '/resources';
        } else {
            const data = await response.json();
            alert(data.message || 'Failed to delete resource');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to delete resource');
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 