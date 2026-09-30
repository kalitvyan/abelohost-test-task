CREATE TABLE posts (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug         VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    title        VARCHAR(255) NOT NULL,
    description  VARCHAR(500) NOT NULL,
    content      MEDIUMTEXT   NOT NULL,
    image        VARCHAR(255) NULL,
    views        INT UNSIGNED NOT NULL DEFAULT 0,
    published_at DATETIME     NOT NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_posts_slug (slug),
    KEY idx_posts_published_at (published_at),
    KEY idx_posts_views (views)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
