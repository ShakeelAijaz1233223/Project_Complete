<?php
session_start();
include "../config/db.php";

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $music_id = mysqli_real_escape_string($conn, $_POST['music_id']);
    $rating = (int)$_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    $review_query = "INSERT INTO reviews (music_id, rating, comment) VALUES ('$music_id', '$rating', '$comment')";

    if (mysqli_query($conn, $review_query)) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=success");
        exit();
    }
}

// Fetch Music with Average Ratings
$query = "SELECT music.*, 
          (SELECT AVG(rating) FROM reviews WHERE reviews.music_id = music.id) as avg_rating,
          (SELECT COUNT(*) FROM reviews WHERE reviews.music_id = music.id) as total_reviews
          FROM music ORDER BY id DESC";
$music = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Studio | Pro Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #0d0d0d;
            --card: #1b1b1b;
            --accent: #ff3366;
            --accent-grad: linear-gradient(135deg, #ff3366, #ff9933);
            --text-main: #f5f5f5;
            --text-muted: #999;
            --shadow: rgba(0, 0, 0, 0.8);
        }

        body {
            background: var(--bg);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        .studio-wrapper {
            width: 95%;
            margin: 0 auto;
            padding: 25px 0;
        }

        /* --- Header --- */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #222;
            padding-bottom: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            background: #1f1f1f;
            border: 1px solid #333;
            color: var(--text-main);
            border-radius: 10px;
            padding: 8px 16px;
            width: 280px;
            transition: 0.3s;
        }

        .search-box:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 12px rgba(255, 51, 102, 0.3);
        }

        /* --- Grid & Cards --- */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .music-card {
            background: var(--card);
            border-radius: 20px;
            padding: 12px;
            border: 1px solid #2a2a2a;
            box-shadow: 0 10px 20px var(--shadow);
            transition: all 0.3s ease;
            position: relative;
        }

        .music-card:hover {
            transform: translateY(-8px);
            border-color: var(--accent);
        }

        /* --- Vinyl & Media --- */
        .media-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 1/1;
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .cover-image {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .cover-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 15px;
            opacity: 0.8;
            transition: 0.5s;
        }

        .music-card.playing .cover-image img {
            opacity: 0.4;
            filter: blur(2px);
        }

        .vinyl-disc {
            width: 85%;
            height: 85%;
            border-radius: 50%;
            position: relative;
            z-index: 2;

            /* 1. Base Vinyl Color & Realistic Grooves */
            background:
                radial-gradient(circle, #ff3366 12%, #aa1e46 13%, transparent 14%),
                /* Center Label */
                radial-gradient(circle, #000 15%, transparent 16%),
                /* Spindle Hole */
                repeating-radial-gradient(circle, #0a0a0a 0px, #0a0a0a 1px, #151515 2px, #0a0a0a 3px);
            /* Micro Grooves */

            border: 5px solid #111;
            box-shadow:
                0 0 25px rgba(0, 0, 0, 0.8),
                inset 0 0 15px rgba(255, 255, 255, 0.05);

            /* Animation Properties */
            opacity: 0;
            transform: scale(0.4) rotate(-20deg);
            transition: all 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            animation: rotate 3s linear infinite;
            animation-play-state: paused;
        }

        /* 2. Shiny "Anisotropic" Reflection (Asli Chamak) */
        .vinyl-disc::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 50%;
            /* Yeh gradient light reflection create karta hai */
            background: conic-gradient(from 0deg,
                    transparent 0deg,
                    rgba(255, 255, 255, 0.05) 45deg,
                    transparent 90deg,
                    rgba(255, 255, 255, 0.05) 135deg,
                    transparent 180deg,
                    rgba(255, 255, 255, 0.05) 225deg,
                    transparent 270deg,
                    rgba(255, 255, 255, 0.05) 315deg,
                    transparent 360deg);
            z-index: 3;
        }

        /* 3. Outer Rim Shadow for 3D Depth */
        .vinyl-disc::before {
            content: "";
            position: absolute;
            inset: -2px;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 1;
        }

        /* Playing State Changes */
        .music-card.playing .vinyl-disc {
            opacity: 1;
            transform: scale(1) rotate(0deg);
            animation-play-state: running;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .play-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 55px;
            height: 55px;
            background: var(--accent-grad);
            border-radius: 50%;
            border: none;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            cursor: pointer;
            z-index: 10;
            box-shadow: 0 0 15px rgba(255, 51, 102, 0.5);
        }

        .custom-controls {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.9));
            padding: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 11;
            opacity: 0;
            transition: 0.3s;
        }

        .media-wrapper:hover .custom-controls {
            opacity: 1;
        }

        .progress {
            flex: 1;
            height: 5px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        /* --- Details --- */
        .title {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            margin: 0;
        }

        .meta-info {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 8px 0;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .artist-tag {
            color: var(--accent) !important;
            font-weight: 600;
            background: rgba(255, 51, 102, 0.1) !important;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .stars-display {
            color: #ffd700;
            font-size: 0.8rem;
            margin-bottom: 10px;
        }

        .rev-btn {
            width: 100%;
            padding: 8px;
            border-radius: 10px;
            border: none;
            background: #222;
            color: #fff;
            font-size: 0.8rem;
            transition: 0.3s;
            margin-bottom: 8px;
        }

        .rev-btn:hover {
            background: var(--accent);
        }

        /* --- Modal --- */
        #reviewOverlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(8px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .review-box {
            background: #151515;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 400px;
            border: 1px solid #333;
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 5px;
            margin-bottom: 20px;
        }

        .star-rating label {
            font-size: 2.5rem;
            color: #333;
            cursor: pointer;
        }

        .star-rating input:checked~label,
        .star-rating label:hover,
        .star-rating label:hover~label {
            color: #ffd700;
        }

        .star-rating input {
            display: none;
        }

        @media (max-width: 480px) {
            .grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }

            .play-btn {
                width: 40px;
                height: 40px;
                font-size: 1.1rem;
            }
        }
    </style>
</head>

<body>

    <div class="studio-wrapper">
        <div class="header-section">
            <h4 class="m-0 fw-bold">Music<span style="color: var(--accent);">Studio</span></h4>
            <div class="d-flex gap-2">
                <input type="text" id="search" class="search-box" placeholder="Search tracks or artists...">
                <a href="index.php" class="btn-back" style="text-decoration:none; color:white; background:#222; padding:8px 15px; border-radius:10px;"><i class="bi bi-house"></i> Home</a>
            </div>
        </div>

        <div class="grid" id="musicGrid">
            <?php while ($row = mysqli_fetch_assoc($music)):
                $avg = round($row['avg_rating']);
            ?>
                <div class="music-card" data-search="<?= strtolower(htmlspecialchars($row['title'] . ' ' . $row['artist'])); ?>">
                    <div class="media-wrapper">
                        <div class="cover-image">
                            <img src="../admin/uploads/music_covers/<?= $row['cover_image'] ?? 'default.jpg' ?>" alt="Cover">
                        </div>
                        <div class="media-wrapper">
                            <div class="cover-image">
                                <img src="your-image.jpg" alt="Cover">
                            </div>

                            <div class="vinyl-disc"></div>

                            <button class="play-btn">
                                <i class="bi bi-play-fill"></i>
                            </button>
                        </div>

                        <button class="play-btn" onclick="toggleAudio('<?= $row['id'] ?>', this)">
                            <i class="bi bi-play-fill"></i>
                        </button>

                        <div class="custom-controls">
                            <input type="range" class="progress" min="0" max="100" value="0">
                            <button class="btn btn-sm text-white border-0" onclick="muteAudio('<?= $row['id'] ?>', this)">
                                <i class="bi bi-volume-up"></i>
                            </button>
                        </div>
                    </div>

                    <p class="title"><?= htmlspecialchars($row['title']) ?></p>
                    <div class="meta-info">
                        <span class="artist-tag"><?= htmlspecialchars($row['artist']) ?></span>
                        <span class="badge bg-dark"><?= htmlspecialchars($row['year']) ?></span>
                    </div>

                    <div class="stars-display">
                        <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                        <small style="color:#666">(<?= $row['total_reviews'] ?>)</small>
                    </div>

                    <button class="rev-btn" onclick="openReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">
                        <i class="bi bi-chat-square-text me-2"></i>REVIEW
                    </button>

                    <audio id="audio-<?= $row['id'] ?>" preload="none">
                        <source src="../admin/uploads/music/<?= $row['file'] ?>" type="audio/mpeg">
                    </audio>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div id="reviewOverlay">
        <div class="review-box">
            <h5 class="text-center" id="revTitle">Track Name</h5>
            <form method="POST">
                <input type="hidden" name="music_id" id="revMusicId">
                <div class="star-rating">
                    <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                    <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                    <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                    <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                    <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
                </div>
                <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" rows="3" placeholder="Write a review..." required></textarea>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary w-100" onclick="closeReview()">CLOSE</button>
                    <button type="submit" name="submit_review" class="btn btn-danger w-100">POST</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Search
        document.getElementById("search").addEventListener("input", function() {
            let val = this.value.toLowerCase();
            document.querySelectorAll(".music-card").forEach(card => {
                card.style.display = card.getAttribute('data-search').includes(val) ? "block" : "none";
            });
        });

        // Audio Logic
        function toggleAudio(id, btn) {
            const audio = document.getElementById('audio-' + id);
            const card = btn.closest('.music-card');
            const icon = btn.querySelector('i');

            document.querySelectorAll('audio').forEach(a => {
                if (a !== audio) {
                    a.pause();
                    a.closest('.music-card').classList.remove('playing');
                    a.closest('.music-card').querySelector('.play-btn i').className = 'bi bi-play-fill';
                }
            });

            if (audio.paused) {
                audio.play();
                card.classList.add('playing');
                icon.className = 'bi bi-pause-fill';
            } else {
                audio.pause();
                card.classList.remove('playing');
                icon.className = 'bi bi-play-fill';
            }
        }

        // Progress update
        document.querySelectorAll('audio').forEach(audio => {
            const progress = audio.closest('.music-card').querySelector('.progress');
            audio.addEventListener('timeupdate', () => {
                progress.value = (audio.currentTime / audio.duration) * 100 || 0;
            });
            progress.addEventListener('input', () => {
                audio.currentTime = (progress.value / 100) * audio.duration;
            });
        });

        function muteAudio(id, btn) {
            const audio = document.getElementById('audio-' + id);
            audio.muted = !audio.muted;
            btn.innerHTML = audio.muted ? '<i class="bi bi-volume-mute"></i>' : '<i class="bi bi-volume-up"></i>';
        }

        function openReview(id, title) {
            document.getElementById('revMusicId').value = id;
            document.getElementById('revTitle').innerText = title;
            document.getElementById('reviewOverlay').style.display = 'flex';
        }

        function closeReview() {
            document.getElementById('reviewOverlay').style.display = 'none';
        }
    </script>
</body>

</html>