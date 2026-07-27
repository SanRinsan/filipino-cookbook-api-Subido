# Create and open README.md for editing
echo "# Filipino Cookbook API" > README.md
echo "" >> README.md
echo "A RESTful API built with Slim Framework for managing Filipino recipes." >> README.md
echo "" >> README.md
echo "## Features" >> README.md
echo "- Create, read, update, delete recipes" >> README.md
echo "- Search by ingredient, region, or category" >> README.md
echo "- User authentication" >> README.md
echo "" >> README.md
echo "## Technologies" >> README.md
echo "- PHP 8.x" >> README.md
echo "- Slim Framework 4" >> README.md
echo "- MySQL" >> README.md
echo "" >> README.md
echo "## Installation" >> README.md
echo '```bash' >> README.md
echo "composer install" >> README.md
echo "cp .env.example .env" >> README.md
echo "php -S localhost:8000 -t public" >> README.md
echo '```' >> README.md
echo "" >> README.md
echo "## API Endpoints" >> README.md
echo "- \`GET /api/recipes\` - Get all recipes" >> README.md
echo "- \`GET /api/recipes/{id}\` - Get single recipe" >> README.md
echo "- \`POST /api/recipes\` - Create new recipe" >> README.md
echo "- \`PUT /api/recipes/{id}\` - Update recipe" >> README.md
echo "- \`DELETE /api/recipes/{id}\` - Delete recipe" >> README.md