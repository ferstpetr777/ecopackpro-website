<?php
/**
 * Template Name: Modern Blog Page
 * 
 * Современная страница блога с красивыми карточками статей
 */

get_header(); ?>

<div class="modern-blog-container">
    <!-- Заголовок блога -->
    <div class="blog-header">
        <div class="container">
            <h1 class="blog-title">Блог EcopackPro</h1>
            <p class="blog-subtitle">Упаковочные материалы и решения</p>
            
            <!-- Поиск по блогу -->
            <div class="blog-search">
                <input type="text" id="blog-search" placeholder="Поиск по блогу..." />
                <button type="button" class="search-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Категории фильтры -->
            <div class="blog-categories">
                <button class="category-btn active" data-category="all">Все статьи</button>
                <button class="category-btn" data-category="packaging">Упаковка</button>
                <button class="category-btn" data-category="boxes">Коробки</button>
                <button class="category-btn" data-category="envelopes">Конверты</button>
                <button class="category-btn" data-category="seals">Пломбы</button>
            </div>
        </div>
    </div>

    <!-- Основной контент -->
    <div class="blog-content">
        <div class="container">
            <div class="articles-grid" id="articles-grid">
                <?php
                // Получаем все статьи
                $articles_query = new WP_Query(array(
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'posts_per_page' => 50,
                    'orderby' => 'date',
                    'order' => 'DESC'
                ));

                if ($articles_query->have_posts()) :
                    while ($articles_query->have_posts()) : $articles_query->the_post();
                        $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                        $post_categories = get_the_category();
                        $category_class = '';
                        
                        if (!empty($post_categories)) {
                            $category_name = strtolower($post_categories[0]->name);
                            if (strpos($category_name, 'упаковка') !== false || strpos($category_name, 'пакет') !== false) {
                                $category_class = 'packaging';
                            } elseif (strpos($category_name, 'коробка') !== false) {
                                $category_class = 'boxes';
                            } elseif (strpos($category_name, 'конверт') !== false) {
                                $category_class = 'envelopes';
                            } elseif (strpos($category_name, 'пломба') !== false) {
                                $category_class = 'seals';
                            }
                        }
                ?>
                    <article class="article-card <?php echo $category_class; ?>" data-title="<?php echo esc_attr(get_the_title()); ?>">
                        <div class="card-image">
                            <?php if ($featured_image) : ?>
                                <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy">
                            <?php else : ?>
                                <div class="no-image">
                                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                        <polyline points="21,15 16,10 5,21"></polyline>
                                    </svg>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Категория тег -->
                            <div class="card-category">
                                <?php if (!empty($post_categories)) : ?>
                                    <span class="category-tag"><?php echo esc_html($post_categories[0]->name); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card-content">
                            <h3 class="card-title"><?php echo get_the_title(); ?></h3>
                            <p class="card-date"><?php echo get_the_date('d.m.Y'); ?></p>
                            
                            <div class="card-actions">
                                <a href="<?php echo get_permalink(); ?>" class="read-btn">
                                    Читать статью
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M5 12h14M12 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php 
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
            
            <!-- Пагинация -->
            <div class="blog-pagination">
                <button class="load-more-btn" id="load-more">Загрузить еще</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Современные стили для блога */
.modern-blog-container {
    min-height: 100vh;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 40px 0;
}

.blog-header {
    background: white;
    padding: 60px 0;
    margin-bottom: 40px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.blog-title {
    font-size: 3rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 10px;
    text-align: center;
}

.blog-subtitle {
    font-size: 1.2rem;
    color: #7f8c8d;
    text-align: center;
    margin-bottom: 40px;
}

.blog-search {
    display: flex;
    justify-content: center;
    margin-bottom: 40px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.blog-search input {
    flex: 1;
    padding: 15px 20px;
    border: 2px solid #e1e8ed;
    border-radius: 25px 0 0 25px;
    font-size: 16px;
    outline: none;
    transition: border-color 0.3s ease;
}

.blog-search input:focus {
    border-color: #3498db;
}

.search-btn {
    padding: 15px 20px;
    background: #3498db;
    border: none;
    border-radius: 0 25px 25px 0;
    color: white;
    cursor: pointer;
    transition: background 0.3s ease;
}

.search-btn:hover {
    background: #2980b9;
}

.blog-categories {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 10px;
}

.category-btn {
    padding: 10px 20px;
    border: 2px solid #e1e8ed;
    background: white;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
    color: #7f8c8d;
}

.category-btn:hover,
.category-btn.active {
    background: #3498db;
    border-color: #3498db;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
}

.articles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.article-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    opacity: 1;
    transform: translateY(0);
}

.article-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.article-card.hidden {
    opacity: 0;
    transform: translateY(20px);
    pointer-events: none;
}

.card-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.article-card:hover .card-image img {
    transform: scale(1.05);
}

.no-image {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    background: #f8f9fa;
    color: #adb5bd;
}

.card-category {
    position: absolute;
    top: 15px;
    left: 15px;
}

.category-tag {
    background: rgba(52, 152, 219, 0.9);
    color: white;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 500;
}

.card-content {
    padding: 25px;
}

.card-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 10px;
    line-height: 1.4;
}

.card-date {
    color: #7f8c8d;
    font-size: 14px;
    margin-bottom: 20px;
}

.card-actions {
    display: flex;
    justify-content: flex-end;
}

.read-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.read-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    color: white;
    text-decoration: none;
}

.load-more-btn {
    display: block;
    margin: 0 auto;
    padding: 15px 40px;
    background: #3498db;
    color: white;
    border: none;
    border-radius: 25px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.load-more-btn:hover {
    background: #2980b9;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

/* Адаптивность */
@media (max-width: 768px) {
    .blog-title {
        font-size: 2rem;
    }
    
    .articles-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .blog-categories {
        justify-content: flex-start;
        overflow-x: auto;
        padding-bottom: 10px;
    }
    
    .category-btn {
        white-space: nowrap;
        flex-shrink: 0;
    }
}

/* Анимации */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.article-card {
    animation: fadeInUp 0.6s ease forwards;
}

.article-card:nth-child(1) { animation-delay: 0.1s; }
.article-card:nth-child(2) { animation-delay: 0.2s; }
.article-card:nth-child(3) { animation-delay: 0.3s; }
.article-card:nth-child(4) { animation-delay: 0.4s; }
.article-card:nth-child(5) { animation-delay: 0.5s; }
.article-card:nth-child(6) { animation-delay: 0.6s; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('blog-search');
    const categoryBtns = document.querySelectorAll('.category-btn');
    const articleCards = document.querySelectorAll('.article-card');
    
    // Поиск по статьям
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        articleCards.forEach(card => {
            const title = card.dataset.title.toLowerCase();
            if (title.includes(searchTerm)) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    });
    
    // Фильтрация по категориям
    categoryBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Убираем активный класс со всех кнопок
            categoryBtns.forEach(b => b.classList.remove('active'));
            // Добавляем активный класс к текущей кнопке
            this.classList.add('active');
            
            const category = this.dataset.category;
            
            articleCards.forEach(card => {
                if (category === 'all' || card.classList.contains(category)) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        });
    });
});
</script>

<?php get_footer(); ?>



