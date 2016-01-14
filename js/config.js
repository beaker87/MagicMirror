var config = {
    lang: 'en',
    time: {
        timeFormat: 24
    },
    weather: {
        //change weather params here:
        //units: metric or imperial
        params: {
            q: 'Bristol,GB',
            units: 'metric',
            // if you want a different lang for the weather that what is set above, change it here
            lang: 'en',
            APPID: '6ffb3bbc268fbc6363f6d372555a95b5'
        }
    },
    compliments: {
        interval: 30000,
        fadeInterval: 4000,
        morning: [
            'Good morning, handsome!',
            'Enjoy your day!',
            'How was your sleep?'
        ],
        afternoon: [
            'Hello, beauty!',
            'You look sexy!',
            'Looking good today!'
        ],
        evening: [
            'Wow, you look hot!',
            'You look nice!',
            'Hi, sexy!'
        ]
    },
    calendar: {
        maximumEntries: 10,
        url: "https://calendar.google.com/calendar/ical/3cqqsn98rlc2d9ckjk2jemlh3o%40group.calendar.google.com/private-70f15e309c882d218c40bfe70261abdf/basic.ics"
    },
	map: {
		apikey: "AIzaSyBZHzo_XmB1Ewf06o7cesEWCQHmMkpQySc"
	},
    news: {
    	feed: 'http://feeds.bbci.co.uk/news/rss.xml?edition=uk'
    }
}
