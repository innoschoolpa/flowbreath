<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <h1>Batch Operations</h1>
    
    <div class="row mt-4">
        <!-- Resources Section -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Resources</h5>
                </div>
                <div class="card-body">
                    <h6>Import Resources</h6>
                    <form action="/batch/import-resources" method="POST" enctype="multipart/form-data" class="mb-4">
                        <div class="mb-3">
                            <label for="resources_csv" class="form-label">CSV File</label>
                            <input type="file" class="form-control" id="resources_csv" name="csv_file" accept=".csv" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </form>

                    <h6>Export Resources</h6>
                    <a href="/batch/export-resources" class="btn btn-secondary">Export to CSV</a>

                    <hr>

                    <h6>Cleanup</h6>
                    <button onclick="cleanupOrphanedResources()" class="btn btn-warning">Cleanup Orphaned Resources</button>
                </div>
            </div>
        </div>

        <!-- Tags Section -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tags</h5>
                </div>
                <div class="card-body">
                    <h6>Import Tags</h6>
                    <form action="/batch/import-tags" method="POST" enctype="multipart/form-data" class="mb-4">
                        <div class="mb-3">
                            <label for="tags_csv" class="form-label">CSV File</label>
                            <input type="file" class="form-control" id="tags_csv" name="csv_file" accept=".csv" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </form>

                    <h6>Export Tags</h6>
                    <a href="/batch/export-tags" class="btn btn-secondary">Export to CSV</a>

                    <hr>

                    <h6>Cleanup</h6>
                    <button onclick="cleanupUnusedTags()" class="btn btn-warning">Cleanup Unused Tags</button>

                    <hr>

                    <h6>Merge Tags</h6>
                    <form action="/batch/merge-tags" method="POST" class="mb-4">
                        <div class="mb-3">
                            <label for="source_tag" class="form-label">Source Tag ID</label>
                            <input type="number" class="form-control" id="source_tag" name="source_id" required>
                        </div>
                        <div class="mb-3">
                            <label for="target_tag" class="form-label">Target Tag ID</label>
                            <input type="number" class="form-control" id="target_tag" name="target_id" required>
                        </div>
                        <button type="submit" class="btn btn-danger">Merge Tags</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cleanupOrphanedResources() {
    if (confirm('Are you sure you want to cleanup orphaned resources? This action cannot be undone.')) {
        fetch('/batch/cleanup-orphaned-resources', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Cleanup completed successfully');
            } else {
                alert('Cleanup failed: ' + data.error);
            }
        })
        .catch(error => {
            alert('An error occurred: ' + error);
        });
    }
}

function cleanupUnusedTags() {
    if (confirm('Are you sure you want to cleanup unused tags? This action cannot be undone.')) {
        fetch('/batch/cleanup-unused-tags', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Cleanup completed successfully');
            } else {
                alert('Cleanup failed: ' + data.error);
            }
        })
        .catch(error => {
            alert('An error occurred: ' + error);
        });
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 