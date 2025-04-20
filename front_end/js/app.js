document.addEventListener('DOMContentLoaded', () => {
    const categoryList = document.getElementById('category-list');
    const courseGrid = document.getElementById('course-grid');
    const API_URL = 'http://cc.localhost';
    let allCategories = []; // Store categories for hierarchy mapping
    let allCourses = []; // Store all courses for counting

    // Fetch all courses once to compute direct counts
    fetch(`${API_URL}/courses`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(courses => {
            allCourses = courses;
            // Fetch and display categories after courses are loaded
            fetchCategories();
        })
        .catch(error => {
            console.error('Error fetching all courses:', error);
            fetchCategories(); // Proceed even if courses fail
        });

    function fetchCategories() {
        fetch(`${API_URL}/categories`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(categories => {
                console.log('Categories:', categories);
                allCategories = categories; // Store for later use
                renderCategories(categories);
            })
            .catch(error => console.error('Error fetching categories:', error));
    }

    // Fetch and display all courses initially
    fetchCourses();

    function renderCategories(categories) {
        categoryList.innerHTML = '';
        const categoryMap = {};

        // Compute direct course counts for each category
        const directCounts = {};
        categories.forEach(category => {
            directCounts[category.id] = 0;
        });
        allCourses.forEach(course => {
            if (directCounts[course.category_id] !== undefined) {
                directCounts[course.category_id]++;
            }
        });

        // Organize categories into a hierarchy
        categories.forEach(category => {
            categoryMap[category.id] = { ...category, subcategories: [] };
        });

        categories.forEach(category => {
            if (category.parent_id && categoryMap[category.parent_id]) {
                categoryMap[category.parent_id].subcategories.push(categoryMap[category.id]);
            }
        });

        // Recursive function to render categories
        function renderCategory(category, depth = 0) {
            const li = document.createElement('li');
            // Use count_of_courses for main categories (depth 0), direct count for subcategories
            const courseCount = depth === 0 ? category.count_of_courses : directCounts[category.id];
            li.innerHTML = `
                <a href="#" class="category depth-${depth}" data-id="${category.id}">
                    ${category.name} (${courseCount})
                </a>
            `;
            if (category.subcategories.length > 0) {
                const ul = document.createElement('ul');
                category.subcategories.forEach(sub => {
                    ul.appendChild(renderCategory(sub, depth + 1));
                });
                li.appendChild(ul);
            }
            return li;
        }

        // Render top-level categories
        Object.values(categoryMap).forEach(category => {
            if (!category.parent_id) {
                categoryList.appendChild(renderCategory(category));
            }
        });

        // Add click event listeners for category filtering
        categoryList.addEventListener('click', (e) => {
            e.preventDefault();
            const target = e.target;
            if (target.tagName === 'A') {
                // Remove active class from all links
                document.querySelectorAll('.sidebar a').forEach(link => {
                    link.classList.remove('active');
                });
                // Add active class to the clicked link
                target.classList.add('active');
                // Fetch courses for the clicked category
                const categoryId = target.getAttribute('data-id');
                fetchCourses(categoryId);
            }
        });
    }

    function fetchCourses(categoryId = null) {
        const url = categoryId ? `${API_URL}/courses?category_id=${categoryId}` : `${API_URL}/courses`;
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(courses => {
                console.log(`Courses for category ${categoryId || 'all'} (direct courses only):`, courses);
                if (categoryId) {
                    renderCoursesHierarchical(categoryId, courses);
                } else {
                    renderCourses(courses); // Flat rendering for "All Courses"
                }
            })
            .catch(error => {
                console.error('Error fetching courses:', error);
                renderCourses([]); // Render empty state on error
            });
    }

    function renderCoursesHierarchical(selectedCategoryId, courses) {
        courseGrid.innerHTML = '';

        // Build category hierarchy (needed to find the selected category)
        const categoryMap = {};
        allCategories.forEach(category => {
            categoryMap[category.id] = { ...category, subcategories: [], courses: [] };
        });

        allCategories.forEach(category => {
            if (category.parent_id && categoryMap[category.parent_id]) {
                categoryMap[category.parent_id].subcategories.push(categoryMap[category.id]);
            }
        });

        // Map courses to their categories
        courses.forEach(course => {
            if (categoryMap[course.category_id]) {
                categoryMap[course.category_id].courses.push(course);
            }
        });

        // Find the selected category
        const selectedCategory = categoryMap[selectedCategoryId];
        if (!selectedCategory) {
            const message = document.createElement('p');
            message.textContent = 'Category not found.';
            message.style.color = '#666';
            message.style.textAlign = 'center';
            message.style.marginTop = '20px';
            courseGrid.appendChild(message);
            return;
        }

        // Render only the selected category and its direct courses
        const categoryDiv = document.createElement('div');
        categoryDiv.classList.add('category-level-0');

        // Category header
        const header = document.createElement('h2');
        header.textContent = selectedCategory.name;
        header.classList.add('category-header');
        categoryDiv.appendChild(header);

        // Direct courses for this category
        if (selectedCategory.courses.length > 0) {
            const coursesDiv = document.createElement('div');
            coursesDiv.classList.add('course-grid');
            selectedCategory.courses.forEach(course => {
                const card = document.createElement('div');
                card.classList.add('course-card');
                card.innerHTML = `
                    <img src="${course.image_preview}" alt="${course.title}">
                    <div class="content">
                        <span class="category">${course.main_category_name}</span>
                        <h3>${course.title}</h3>
                        <p>${course.description}</p>
                    </div>
                `;
                coursesDiv.appendChild(card);
            });
            categoryDiv.appendChild(coursesDiv);
        } else {
            const message = document.createElement('p');
            message.textContent = 'No direct courses in this category.';
            message.classList.add('no-courses');
            categoryDiv.appendChild(message);
        }

        courseGrid.appendChild(categoryDiv);
    }

    function renderCourses(courses) {
        courseGrid.innerHTML = '';
        if (courses.length === 0) {
            const message = document.createElement('p');
            message.textContent = 'No courses found.';
            message.style.color = '#666';
            message.style.textAlign = 'center';
            message.style.marginTop = '20px';
            courseGrid.appendChild(message);
            return;
        }
        const grid = document.createElement('div');
        grid.classList.add('course-grid');
        courses.forEach(course => {
            const card = document.createElement('div');
            card.classList.add('course-card');
            card.innerHTML = `
                <img src="${course.image_preview}" alt="${course.title}">
                <div class="content">
                    <span class="category">${course.main_category_name}</span>
                    <h3>${course.title}</h3>
                    <p>${course.description}</p>
                </div>
            `;
            grid.appendChild(card);
        });
        courseGrid.appendChild(grid);
    }
});