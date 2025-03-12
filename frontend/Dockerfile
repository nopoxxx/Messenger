# Используем Node.js образ
FROM node:18

# Устанавливаем рабочую директорию внутри контейнера
WORKDIR /app

# Копируем package.json и package-lock.json
COPY package.json package-lock.json ./

# Устанавливаем зависимости
RUN npm install

# Копируем все файлы в контейнер
COPY . .

# Открываем порт для React-приложения
EXPOSE 3000

# Запускаем dev-сервер React
CMD ["npm", "start"]
