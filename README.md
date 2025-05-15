# ConnectCulture

ConnectCulture is a web application designed to connect people with similar cultural interests, allowing them to form groups, organize events, and share experiences.

## Features

- **User Authentication**: Secure login and registration system
- **Group Management**: Create, join, and manage cultural interest groups
- **Weather Integration**: Real-time weather information for group locations
- **Responsive Design**: Optimized for both desktop and mobile devices
- **Admin Dashboard**: Comprehensive tools for site administration

## Technologies Used

- **Backend**: Symfony 6.4 PHP Framework
- **Frontend**: Twig, HTML5, CSS3, JavaScript
- **Database**: MySQL
- **APIs**: OpenWeatherMap for weather data
- **Authentication**: Symfony Security Bundle

## Installation

### Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL
- Symfony CLI (optional, for development)

### Setup Instructions

1. Clone the repository:
   ```
   git clone https://github.com/yourusername/ConnectCulture.git
   cd ConnectCulture
   ```

2. Install dependencies:
   ```
   composer install
   ```

3. Configure your database in `.env`:
   ```
   DATABASE_URL=mysql://username:password@127.0.0.1:3306/connectculture
   ```

4. Create the database and run migrations:
   ```
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. Load fixtures (optional, for development):
   ```
   php bin/console doctrine:fixtures:load
   ```

6. Start the Symfony development server:
   ```
   symfony server:start
   ```

7. Access the application at `http://localhost:8000`

## Project Structure

- `src/Controller/`: Contains all controllers
- `src/Entity/`: Database entity classes
- `src/Repository/`: Database query classes
- `src/Service/`: Service classes including weather API integration
- `templates/`: Twig templates for the frontend
- `public/`: Publicly accessible files (CSS, JS, images)

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature-name`
3. Commit your changes: `git commit -m 'Add some feature'`
4. Push to the branch: `git push origin feature-name`
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgements

- OpenWeatherMap for providing weather data
- Symfony community for the excellent framework and documentation
- All contributors who have helped improve this project