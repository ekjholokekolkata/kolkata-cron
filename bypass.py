import re
import requests
from Crypto.Cipher import AES

url = "https://ekjholokekolkata.page.gd/central_notifier.php"
headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"}

session = requests.Session()
response = session.get(url, headers=headers).text

# 1. Dynamically parse the encrypted a, b, and c hex blocks from InfinityFree's security challenge
try:
    a_hex = re.search(r'toNumbers\("([a-f0-9]+)"\)', response).group(1)
    # Search globally for b and c structures
    matches = re.findall(r'toNumbers\("([a-f0-9]+)"\)', response)
    b_hex = matches
    c_hex = matches

    # Convert hex values to clean byte strings
    key = bytes.fromhex(a_hex)
    iv = bytes.fromhex(b_hex)
    ciphertext = bytes.fromhex(c_hex)

    # 2. Decrypt the live validation string using AES-128-CBC
    cipher = AES.new(key, AES.MODE_CBC, iv)
    decrypted = cipher.decrypt(ciphertext)
    test_cookie_value = decrypted.hex()

    # 3. Apply the generated cookie to cross the firewall
    session.cookies.set("__test", test_cookie_value, domain="ekjholokekolkata.page.gd", path="/")
    
    # 4. Fire the true notifier endpoint request securely
    final_response = session.get(url, headers=headers)
    print(f"Server Response Content: {final_response.text}")

except Exception as e:
    print(f"Bypass error or target is already open: {str(e)}")
