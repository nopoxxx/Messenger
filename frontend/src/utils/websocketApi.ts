class WebSocketAPI {
	private socket: WebSocket
	private eventHandlers: { [key: string]: (data: any) => void } = {}

	constructor() {
		this.socket = new WebSocket('ws://localhost:8080')

		this.socket.onopen = () => console.log('WebSocket подключен')
		this.socket.onmessage = event => this.handleMessage(event)
		this.socket.onclose = () => console.log('WebSocket отключен')
		this.socket.onerror = error => console.error('WebSocket ошибка:', error)
	}

	private handleMessage(event: MessageEvent) {
		const response = JSON.parse(event.data)
		const { action, data } = response

		if (this.eventHandlers[action]) {
			this.eventHandlers[action](data)
		} else {
			console.warn('Неизвестное событие:', action)
		}
	}

	public on(action: string, callback: (data: any) => void) {
		this.eventHandlers[action] = callback
	}

	public send(action: string, payload: object = {}) {
		const message = JSON.stringify({ action, ...payload })

		if (this.socket.readyState !== WebSocket.OPEN) {
			console.error('WebSocket не подключен, сообщение не отправлено:', message)
			return
		}

		console.log(`📤 Отправляем сообщение в WebSocket:`, message)
		this.socket.send(message)
	}

	public getContacts(userId: number) {
		this.send('getContacts', { userId })
	}

	public addContact(userId: number, contactId: number) {
		this.send('getContacts', { userId })
	}

	public getUsers() {
		this.socket.send(JSON.stringify({ action: 'getUsers' }))
	}

	public getChatMessages(userId: number, contactId: number) {
		this.send('getChatMessages', { userId, contactId })
	}

	public addNewMessage(senderId: number, receiverId: number, message: string) {
		this.send('addNewMessage', { senderId, receiverId, message })
	}

	public editMessage(messageId: number, senderId: number, newMessage: string) {
		this.send('editMessage', { messageId, senderId, message: newMessage })
	}

	public deleteMessage(messageId: number, senderId: number) {
		this.send('deleteMessage', { messageId, senderId })
	}
}

const ws = new WebSocketAPI()

export default ws
