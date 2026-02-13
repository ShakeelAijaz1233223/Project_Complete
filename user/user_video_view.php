<?php
include "../config/db.php";

/* ===== Handle Review ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $video_id = (int)$_POST['video_id'];
    $rating   = (int)$_POST['rating'];
    $comment  = mysqli_real_escape_string($conn, $_POST['comment']);

    mysqli_query($conn, "
        INSERT INTO video_reviews (video_id, rating, comment)
        VALUES ($video_id, $rating, '$comment')
    ");
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=success");
    exit;
}

/* ===== Fetch Videos ===== */
$videos = mysqli_query($conn, "
    SELECT v.*,
    (SELECT AVG(rating) FROM video_reviews WHERE video_id=v.id) avg_rating,
    (SELECT COUNT(*) FROM video_reviews WHERE video_id=v.id) total_reviews
    FROM videos v
    ORDER BY v.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Video Studio | Pro Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

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

        /* --- Header Section --- */
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

        .btn-back {
            background: #222;
            border: none;
            color: var(--text-main);
            padding: 7px 18px;
            border-radius: 10px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            transition: 0.3s;
            white-space: nowrap;
        }

        .btn-back:hover {
            background: var(--accent);
            color: #fff;
        }

        /* --- Grid & Cards --- */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .album-card {
            background: var(--card);
            border-radius: 20px;
            padding: 12px;
            border: 1px solid #2a2a2a;
            box-shadow: 0 10px 20px var(--shadow);
            transition: all 0.3s ease;
            position: relative;
        }

        .album-card:hover {
            transform: translateY(-8px);
            border-color: var(--accent);
        }

        /* --- Media Wrapper (1:1 Ratio) --- */
        .media-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 1 / 1;
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 15px;
        }

        .media-wrapper img,
        .media-wrapper video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
        }

        .media-wrapper img {
            z-index: 2;
            transition: opacity 0.5s ease;
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
            transition: 0.3s;
            box-shadow: 0 0 15px rgba(255, 51, 102, 0.5);
        }

        .custom-controls {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.9));
            padding: 10px;
            z-index: 11;
            opacity: 0;
            transition: 0.3s;
        }

        .media-wrapper:hover .custom-controls {
            opacity: 1;
        }

        .progress {
            width: 100%;
            height: 5px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        /* --- Text & Info Styling --- */
        .title {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            color: #fff;
        }

        .meta-info {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 8px 0 12px;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .meta-info span {
            background: rgba(255, 255, 255, 0.05);
            padding: 3px 6px;
            border-radius: 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .artist-tag {
            color: var(--accent) !important;
            font-weight: 600;
            background: rgba(255, 51, 102, 0.1) !important;
        }

        .stars-display {
            color: #ffd700;
            font-size: 0.8rem;
            margin-bottom: 12px;
        }

        .rev-btn {
            width: 100%;
            padding: 8px;
            border-radius: 10px;
            border: none;
            background: #222;
            color: #fff;
            font-size: 0.8rem;
            font-weight: 600;
            transition: 0.3s;
        }

        .rev-btn:hover {
            background: var(--accent);
        }

        /* --- Review Modal Overlay --- */
        #reviewOverlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(8px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 15px;
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

        .star-rating input {
            display: none;
        }

        .star-rating label:hover,
        .star-rating label:hover~label,
        .star-rating input:checked~label {
            color: #ffd700;
        }

        footer {
            text-align: center;
            padding: 40px;
            color: #444;
            font-size: 0.8rem;
        }

        /* --- RESPONSIVE ADJUSTMENTS --- */
        @media (max-width: 992px) {
            .grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }

            .header-section {
                flex-direction: column;
                align-items: stretch;
            }

            /* .search-box {
                width: 100%;
            } */
        }

        @media (max-width: 768px) {
            .grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
            }

            .play-btn {
                width: 45px;
                height: 45px;
                font-size: 1.3rem;
            }
        }

        @media (max-width: 480px) {
            .grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }

            h4 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body>

    <div class="studio-wrapper">
        <div class="header-section">
            <h4 class="m-0 fw-bold">Video<span style="color:var(--accent)">Studio</span></h4>
            <div class="d-flex gap-2">
                <input id="search" class="search-box" placeholder="Search videos, artists...">
                <a href="index.php" class="btn-back"><i class="bi bi-house"></i> Home</a>
            </div>
        </div>

        <div class="grid" id="grid">
            <?php while ($v = mysqli_fetch_assoc($videos)):
                $thumb = $v['thumbnail'] ? "../admin/uploads/video_thumbnails/" . $v['thumbnail'] : "../assets/img/default.jpg";
                $avg = round($v['avg_rating']);
            ?>
                <div class="album-card" data-search="<?= strtolower($v['title'] . ' ' . $v['artist']) ?>">
                    <div class="media-wrapper">
                        <img src="<?= $thumb ?>" id="t<?= $v['id'] ?>">
                        <video id="v<?= $v['id'] ?>" loop playsinline ontimeupdate="updateProgress(<?= $v['id'] ?>)">
                            <source src="../admin/uploads/videos/<?= $v['file'] ?>" type="video/mp4">
                        </video>

                        <button class="play-btn" onclick="playVid(<?= $v['id'] ?>, this)">
                            <i class="bi bi-play-fill"></i>
                        </button>

                        <div class="custom-controls">
                            <input type="range" class="progress" id="p<?= $v['id'] ?>" min="0" max="100" value="0" oninput="seekVideo(<?= $v['id'] ?>, this.value)">
                        </div>
                    </div>

                    <p class="title"><?= htmlspecialchars($v['title']) ?></p>

                    <div class="meta-info">
                        <span class="artist-tag"><?= htmlspecialchars($v['artist']) ?></span>
                        <span><?= htmlspecialchars($v['album']) ?></span>
                        <span><i class="bi bi-calendar3"></i> <?= $v['year'] ?></span>
                    </div>

                    <div class="stars-display">
                        <?php for ($i = 1; $i <= 5; $i++) echo $i <= $avg ? '★' : '☆'; ?>
                        <span style="color: #555; font-size: 0.7rem;">(<?= $v['total_reviews'] ?>)</span>
                    </div>

                    <button class="rev-btn" onclick="openReview(<?= $v['id'] ?>,'<?= addslashes($v['title']) ?>')">
                        ADD REVIEW
                    </button>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div id="reviewOverlay">
        <div class="review-box">
            <h5 id="rvTitle" class="text-center mb-1"></h5>
            <p class="text-center text-muted small mb-4">Rate your cinematic experience</p>
            <form method="post">
                <input type="hidden" name="video_id" id="rvId">
                <div class="star-rating">
                    <input id="s5" type="radio" name="rating" value="5" required><label for="s5">★</label>
                    <input id="s4" type="radio" name="rating" value="4"><label for="s4">★</label>
                    <input id="s3" type="radio" name="rating" value="3"><label for="s3">★</label>
                    <input id="s2" type="radio" name="rating" value="2"><label for="s2">★</label>
                    <input id="s1" type="radio" name="rating" value="1"><label for="s1">★</label>
                </div>
                <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" rows="3" placeholder="Write your review..." required></textarea>
                <div class="row g-2">
                    <div class="col-6"><button type="button" class="btn btn-secondary w-100" onclick="closeReview()">Cancel</button></div>
                    <div class="col-6"><button type="submit" name="submit_review" class="btn btn-primary w-100" style="background:var(--accent); border:none;">Post</button></div>
                </div>
            </form>
        </div>
    </div>

    <footer>&copy; 2026 Video Studio Pro &bull; Next-Gen Entertainment</footer>

    <script>
        /* Search Logic */
        document.getElementById('search').oninput = function() {
            let val = this.value.toLowerCase();
            document.querySelectorAll('.album-card').forEach(card => {
                card.style.display = card.dataset.search.includes(val) ? 'block' : 'none';
            });
        };

        /* Video Moving Line Logic */
        function updateProgress(id) {
            let video = document.getElementById('v' + id);
            let progress = document.getElementById('p' + id);
            if (video.duration) {
                progress.value = (video.currentTime / video.duration) * 100;
            }
        }

        function seekVideo(id, value) {
            let video = document.getElementById('v' + id);
            video.currentTime = (value / 100) * video.duration;
        }

        /* Play/Pause Logic */
        function playVid(id, btn) {
            let video = document.getElementById('v' + id);
            let thumb = document.getElementById('t' + id);
            let icon = btn.querySelector('i');

            document.querySelectorAll('video').forEach(v => {
                if (v !== video) {
                    v.pause();
                    let wrapper = v.closest('.media-wrapper');
                    wrapper.querySelector('img').style.opacity = 1;
                    wrapper.querySelector('.play-btn i').className = 'bi bi-play-fill';
                }
            });

            if (video.paused) {
                video.play();
                thumb.style.opacity = 0;
                icon.className = 'bi bi-pause-fill';
            } else {
                video.pause();
                thumb.style.opacity = 1;
                icon.className = 'bi bi-play-fill';
            }
        }

        /* Review Logic */
        function openReview(id, title) {
            document.getElementById('rvId').value = id;
            document.getElementById('rvTitle').innerText = title;
            document.getElementById('reviewOverlay').style.display = 'flex';
        }

        function closeReview() {
            document.getElementById('reviewOverlay').style.display = 'none';
        }
    </script>

</body>

</html>