"""the main function that actually invoke the web scraper."""
import argparse
import web_scraper


def main(arg):
    """the main function that actually invoke the web scraper"""
    scraper = web_scraper.WebScraper()
    scraper.scrape(arg.initial_url, arg.num_book, arg.num_author)
    print("finish")


if __name__ == "__main__":
    PARSER = argparse.ArgumentParser(description='WebScraper')

    PARSER.add_argument('--url', dest='initial_url', type=str,
                        default='https://www.goodreads.com/book/show/'
                                '3735293-clean-code?from_search=true&qid=HhMDV0vMa5&rank=1',
                        help='the starting url of scraping')
    PARSER.add_argument('--book', dest='num_book', type=int,
                        default='200',
                        help='number of scraping book')
    PARSER.add_argument('--author', dest='num_author', type=int,
                        default='50',
                        help='number of scraping author')
    ARGS = PARSER.parse_args()
    main(ARGS)
