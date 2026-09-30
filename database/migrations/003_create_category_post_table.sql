CREATE TABLE category_post (
    category_id INT UNSIGNED NOT NULL,
    post_id     INT UNSIGNED NOT NULL,
    PRIMARY KEY (category_id, post_id),
    KEY idx_category_post_post (post_id, category_id),
    CONSTRAINT fk_category_post_category
        FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE,
    CONSTRAINT fk_category_post_post
        FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
