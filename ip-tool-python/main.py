# -*- coding: utf-8 -*-
import sys
import six.moves.urllib.request as request
import json
import socket

get_ip_detail_url = 'https://ipapi.co/{ip}/json'

try:
    want_to_check_ip = sys.argv[1]
except IndexError:
    want_to_check_ip = ''

response = request.urlopen(url=get_ip_detail_url.format(ip=want_to_check_ip))
result = response.read()
result = json.loads(result)

def get_item(title, subtitle=None, icon=None):
    item = {'arg': title, 'title': title, 'text': {'copy': title, 'largetype': title}}
    if subtitle: item['subtitle'] = subtitle
    if icon: item['icon'] = {"type": "png", "path": icon}
    return item


def print_items(items):
    show = {'items': items}
    print(json.dumps(show))


items = []

# Show local ip
local_ip = socket.gethostbyname(socket.gethostname())
items.append(get_item(local_ip, icon='local_ip.png'))

# Show ip
title_ip = '%s' % result.get('ip')
items.append(get_item(title_ip, icon='ip.png'))

# Show country
flag_dict = {
    'CN': '🇨🇳',
    'US': '🇺🇸'
}
country = '{name} {flag}'.format(name=result.get('country_name'), flag=flag_dict.get(result.get('country'), ''))
items.append(get_item(country, result.get('country'), icon='country.png'))

# show location
title_location = '{} {}'.format(result.get('region'), result.get('city'))
items.append(get_item(title_location, result.get('org'), icon='location.png'))

print_items(items=items)
