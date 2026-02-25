# INFO834 – TP1  
## Redis Rate Limiting – EtuServices

---

## Architecture

Browser  
↓  
PHP (Apache)  
↓  
MySQL (Users)  
↓  
Redis (Rate limiting + statistics)

A single Redis instance is used with:

- **DB0** → Login rate limiting
- **DB1** → Service rate limiting & statistics

---

Redis commands used:
- INCR
- EXPIRE
- HINCRBY
- ZINCRBY
- LPUSH
- LTRIM

---

## Installation

### Requirements
- Docker
- Docker Compose

### Run the project

```bash
docker compose down -v
docker compose up -d --build 
```
---

### Access

**Web Application:**  
http://localhost:8000  

**phpMyAdmin:**  
http://localhost:8080  

---

## Test Account

**Email:** omar@gmail.com  
**Password:** 123
