const categoryListDiv = document.getElementById('category-list');
const courseListDiv = document.getElementById('course-list');
const apiUrl = 'http://localhost:8000/';

function fetchCategories() {
    fetch(`${apiUrl}/categories`)
        .then(response => response.json())
        .then(categories => {
            displayCategories(categories);
        })
        .catch(error => console.error('Error fetching categories:', error));
}

function fetchCourses(categoryId = null) {
    let url = `${apiUrl}/courses`;
    if (categoryId) {
        url += `/${categoryId}`;
    }
    fetch(url)
        .then(response => response.json())
        .then(courses => {
            displayCourses(courses);
        })
        .catch(error => console.error('Error fetching courses:', error));
}
function displayCategories(categories, parentElement = categoryListDiv) {
    if (parentElement === categoryListDiv) {
        parentElement.innerHTML = '';
    }

    categories.forEach(category => {
        const wrapper = document.createElement('div');
        wrapper.classList.add('category-item');

        const label = document.createElement('div');
        label.style.cursor = 'pointer';
        if(category.count_of_courses > 0){
            label.textContent = `${category.name} (${category.count_of_courses})`;
        }else{
            label.textContent = `${category.name}`;
        }

        label.addEventListener('click', () => {
            document.getElementById('catalog-title').textContent = category.name;
            fetchCourses(category.id);
        });
        wrapper.appendChild(label);

        if (category.children && category.children.length > 0) {
            const childContainer = document.createElement('div');
            childContainer.classList.add('child-categories');
            displayCategories(category.children, childContainer);
            wrapper.appendChild(childContainer);
        }

        parentElement.appendChild(wrapper);
    });
}
function displayCourses(courses) {
    const courseListDiv = document.getElementById('course-list');
    courseListDiv.innerHTML = '';

    if (courses.length === 0) {
        courseListDiv.innerHTML = '<div class="col-12 text-center">No courses found.</div>';
        return;
    }

    courses.forEach(course => {
        const col = document.createElement('div');
        col.className = 'col-12 col-md-6 col-lg-4 mb-4';

        col.innerHTML = `
            <div class="card h-100 shadow-sm">
             <div class="course-tag">${course.main_category_name}</div>
                <img src="${course.image_preview}" class="card-img-top" alt="${course.title}">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">${course.title}</h5>
                    <p class="card-text">${course.description}</p>
                </div>
            </div>
        `;

        courseListDiv.appendChild(col);
    });
}


fetchCategories();
fetchCourses();

