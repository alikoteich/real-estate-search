# **Property Search Application**

A modern, responsive web application for searching and filtering properties based on user-defined criteria. Built using Symfony and Twig, this project demonstrates the use of reusable components, a clean design, and efficient data handling.

---

## Features

- Property search by type, price, bedrooms, and location.
- Recommendations for better deals and premium options.
- Nearby location suggestions based on user input.
- Data fixtures for testing and demonstration.

## Project Structure
    
    PropertySearch/
    ├── templates/
    │ ├── base.html.twig
    │ ├── search.html.twig
    │ ├── components/
    │ │ └── _recommendations.html.twig
    │ │ └── _search_form.html.twig
    │ │ └── _search_results.html.twig
    ├── public/
    ├── src/
    │ ├── Controller/
    │ │ └── PropertyController.php
    │ ├── Entity/
    │ │ └── Property.php
    │ └── Repository/
    │ └── PropertyRepository.php
    │ ├── Service/
    │ │ └── FilterManager.php
    │ │ └── PropertyQueryParser.php
    │ │ └── RecommendationService.php
## **General Idea**

The **Property Search Application** allows users to efficiently search for and view properties based on their preferences. Users can:

1. Filter properties by type, number of bedrooms, price, and location.
2. View search results in a structured, user-friendly table.
3. Access personalized property recommendations.
4. Enjoy a seamless user experience with a responsive design and reusable components.

---

## Instructions to Run the Code

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/my_project.git
   cd my_project

2. Install dependencies:
   ```bash
   composer install
3. Set up the Database and Load Fixtures:
   
  - Create the database:
    ```bash
     php bin/console doctrine:database:create
   - Run migrations:
     ```bash
     php bin/console doctrine:migrations:migrate
   - Load fixtures:
     ```bash
     php bin/console doctrine:fixtures:load
  
5. Start the development server:
   ```bash
   symfony server:start
