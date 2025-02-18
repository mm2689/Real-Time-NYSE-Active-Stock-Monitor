import requests
from bs4 import BeautifulSoup
from pymongo import MongoClient

# Connect to MongoDB
client = MongoClient('localhost', 27017)
db = client['stock_database']
collection = db['stocks']

# URL of the NYSE most active stocks page
url = 'https://finance.yahoo.com/most-active'

# Send the GET request with a user-agent
headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'}
response = requests.get(url, headers=headers)

# Ensure the request was successful
if response.status_code == 200:
    soup = BeautifulSoup(response.content, 'html.parser')
    
    # Find all the rows in the table with the class 'simpTblRow'
    for row in soup.find_all('tr', class_='simpTblRow'):
        try:
            # Extracting the text from each cell using the relevant class or aria-label
            symbol = row.find('td', attrs={"aria-label": "Symbol"}).text.strip()
            name = row.find('td', attrs={"aria-label": "Name"}).text.strip()
            price = float(row.find('td', attrs={"aria-label": "Price (Intraday)"}).text.strip().replace(',', ''))
            change = row.find('td', attrs={"aria-label": "Change"}).text.strip()
            change = float(change.replace(',', '').replace('%', '')) if '%' in change else float(change.replace(',', ''))
            volume = row.find('td', attrs={"aria-label": "Volume"}).text.strip().replace(',', '')
            volume = int(volume) if volume.isdigit() else 0
            
            # Insert data into MongoDB, including a check to avoid duplicates based on symbol
            if collection.find_one({'symbol': symbol}) is None:
                collection.insert_one({
                    'symbol': symbol,
                    'name': name,
                    'price': price,
                    'change': change,
                    'volume': volume
                })
        except Exception as e:
            print(f"An error occurred while processing row: {e}")
else:
    print(f"Failed to retrieve data, status code: {response.status_code}")

print("Stock data scraped and inserted into MongoDB.")
