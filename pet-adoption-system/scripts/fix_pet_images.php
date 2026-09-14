<?php
require_once __DIR__ . '/../config/database.php';

$dir = __DIR__ . '/../uploads/pets';

if (!is_dir($dir)) {
    echo "Upload directory not found: {$dir}\n";
    exit(1);
}

$updated = 0;
$skipped = 0;

foreach (new DirectoryIterator($dir) as $fileInfo) {
    if ($fileInfo->isDot() || !$fileInfo->isFile()) {
        continue;
    }

    $filename = $fileInfo->getFilename();
    $baseName = pathinfo($filename, PATHINFO_FILENAME);
    $animalId = explode('_', $baseName, 2)[0];

    if (!$animalId) {
        $skipped++;
        continue;
    }

    $photoPath = 'uploads/pets/' . $filename;
    $stmt = $pdo->prepare(
        'UPDATE pets SET photo_path = ? WHERE animal_id = ? AND (photo_path IS NULL OR photo_path = \'\')'
    );

    $stmt->execute([$photoPath, $animalId]);

    if ($stmt->rowCount() > 0) {
        $updated++;
        echo "Updated {$animalId} -> {$photoPath}\n";
    }
}

echo "Finished. Updated {$updated} pet records. Skipped {$skipped} files with no animal ID prefix.\n";
