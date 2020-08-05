""" the actual implementation of the web scraper"""
import queue
import json
import re
import os
import requests
from bs4 import BeautifulSoup
import mysql.connector


def parse_key(key):
    "function to make sure the format of key is acceptable for database"
    new_key = ""
    for i in enumerate(key):
        if i[1] == '.' or i[1] == '*':
            new_key += ' '
        elif i[1] == '/' or i[1] == ':':
            new_key += ' '
        elif i[1] == '?' or i[1] == '|':
            new_key += ' '
        elif i[1] == '%' or i[1] == '"':
            new_key += ' '
        elif i[1] == '<' or i[1] == '>':
            new_key += ' '
        else:
            new_key += i[1]
    return new_key

def find_related_author_name(related_author, related_authors):
    """find the related authors' name and place into the list related_authors"""
    for author in related_author:
        scripts = author.findAll('script')
        for script in scripts:
            name = ""
            start = str(script).find("authorName")
            find_name = False
            if start != -1:
                while start < 1000:
                    if str(script)[start] == '<':
                        break
                    if find_name:
                        name = name + str(script)[start]
                    if str(script)[start] == '>':
                        find_name = True
                    start += 1
            if name != "":
                related_authors.append(name)


class WebScraper:
    """ the class for the web scraper"""

    def __init__(self):
        self.url_list = []
        self.author_list = []
        self.book_name_list = []
        self.process_list = queue.Queue()
        self.book_count = 0
        self.author_count = 0
        self.database = mysql.connector.connect(
            host="localhost",
            user="root",
            password="XzY990913"
        )
        mycursor = self.database.cursor()
        mycursor.execute("CREATE DATABASE IF NOT EXISTS projectDatabase")
        self.database = mysql.connector.connect(
            host="localhost",
            user="root",
            password="XzY990913",
            database="projectDatabase"
        )
        mycursor = self.database.cursor()
        mycursor.execute("SHOW TABLES LIKE 'Books'")
        result = mycursor.fetchone()
        if not result:
            mycursor.execute(
                "CREATE TABLE Books (book_id varchar(255) NOT NULL, name VARCHAR(255), author VARCHAR(255), rating float(20), rating_cnt int, review_cnt int, PRIMARY KEY(book_id))")
        else:
            mycursor.execute("DELETE FROM Books")
            self.database.commit()
        mycursor.execute("SHOW TABLES LIKE 'Author'")
        result = mycursor.fetchone()
        if not result:
            mycursor.execute(
                "CREATE TABLE Author (author_id varchar(255) NOT NULL, name VARCHAR(255), rating float, rating_cnt int, PRIMARY KEY(author_id))")
        else:
            mycursor.execute("DELETE FROM Author")
            self.database.commit()
        mycursor.execute("SHOW TABLES LIKE 'SimilarBooks'")
        result = mycursor.fetchone()
        if not result:
            mycursor.execute(
                "CREATE TABLE SimilarBooks (book_id_1 varchar(255) NOT NULL, book_id_2 VARCHAR(255) NOT NULL)")
        else:
            mycursor.execute("DELETE FROM SimilarBooks")
            self.database.commit()
        mycursor.execute("SHOW TABLES LIKE 'FriendsAuthor'")
        result = mycursor.fetchone()
        if not result:
            mycursor.execute(
                "CREATE TABLE FriendsAuthor (author_id_1 varchar(255) NOT NULL, author_id_2 VARCHAR(255) NOT NULL)")
        else:
            mycursor.execute("DELETE FROM FriendsAuthor")
            self.database.commit()

    def scrape(self, url, num_book, num_author):
        """the main functional function to do the scraping"""
        self.url_list.append(url)
        self.process_list.put(url)
        if num_book > 2000 or num_author > 2000:
            print("too many requests")
            return
        while not self.process_list.empty():
            print(self.book_count)
            print(self.author_count)
            if self.book_count >= num_book and self.author_count >= num_author:
                return
            processing_url = self.process_list.get()
            page = requests.get(processing_url)
            soup = BeautifulSoup(page.content, 'html.parser')
            book_result = soup.find(id='bookTitle')
            author_result = soup.find('a', class_='authorName')
            if None in (book_result, author_result):
                book_result = soup.find(id='bookTitle')
                author_result = soup.find(id='bookAuthors')
            if None in (book_result, author_result):
                continue
            other_books = soup.findAll('div', class_='bookCover')
            if not other_books:
                other_books = soup.findAll('li', class_='cover')
            if not other_books:
                continue
            if book_result not in self.book_name_list:
                self.book_name_list.append(book_result)
                self.book_count += 1
                self.add_book({}, book_result, processing_url, soup)
            if author_result not in self.author_list:
                self.author_list.append(author_result)
                self.author_count += 1
                self.add_author({}, author_result, soup)
            for book in other_books:
                link = book.find('a')['href']
                if link not in self.url_list:
                    self.url_list.append(link)
                    self.process_list.put(link)
        if self.process_list.empty():
            print("not enough number")
            new_url = input("please enter url:")
            self.scrape(new_url, num_book, num_author)
            return
        return

    def add_book(self, dicts, result, address, soup):
        """add the book with url in address and title in result to the dictionary."""
        similar_list = soup.findAll('li', class_='cover')
        mycursor = self.database.cursor()
        for book in similar_list:
            book_page = requests.get(book.find('a')['href'])
            soup1 = BeautifulSoup(book_page.content, 'html.parser')
            sql1 = "INSERT INTO SimilarBooks (book_id_1, book_id_2) VALUES (%s, %s)"
            val1 = (soup.find('input', id='book_id').get('value'), soup1.find('input', id='book_id').get('value'))
            mycursor.execute(sql1, val1)
            self.database.commit()
        sql = "INSERT INTO Books (book_id, name, author, rating, rating_cnt, review_cnt) VALUES (%s, %s, %s, %s, %s, %s)"
        val = (soup.find('input', id='book_id').get('value'), result.text.replace("\n", "").strip(), soup.find('a', class_='authorName').text, str(float(soup.find(itemprop='ratingValue').text.replace("\r\n", ""))), soup.find(itemprop='ratingCount')['content'], soup.find(itemprop='reviewCount')['content'])
        mycursor.execute(sql, val)

        self.database.commit()

    def add_author(self, dicts, result, soup):
        """add the author information to the corresponding dictionary"""
        mycursor = self.database.cursor()
        author_url = result['href']
        author_page = requests.get(author_url)
        soup1 = BeautifulSoup(author_page.content, 'html.parser')
        author_name = soup1.find(itemprop='name').text
        related_author = soup.find('div', class_='bookCarousel')
        related_author = related_author.findAll('li', class_='cover')
        related_authors = []
        find_related_author_name(related_author, related_authors)
        for author in related_authors:
            sql1 = "INSERT INTO FriendsAuthor (author_id_1, author_id_2) VALUES (%s, %s)"
            val1 = (author_name, author)
            mycursor.execute(sql1, val1)
            self.database.commit()
        sql = "INSERT INTO Author(author_id, name, rating, rating_cnt) VALUES (%s, %s, %s, %s)"
        val = (re.findall(r'\d+', author_url)[0], author_name, str(float(soup1.find(itemprop='ratingValue').text)), soup.find(itemprop='ratingCount')['content'])
        mycursor.execute(sql, val)
        self.database.commit()


