class WebSocketAPI {
	private socket: WebSocket
	private eventHandlers: { [key: string]: (data: any) => void } = {}

	constructor() {
		this.socket = new WebSocket('ws://localhost:8080')

		this.socket.onopen = () => this.auth()
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

		this.socket.send(message)
	}

	private auth(
		token = document.cookie
			.split('; ')
			.find(row => row.startsWith('token='))
			?.split('=')[1]
	) {
		this.send('auth', { token })
	}

	public setProfile(avatar: any, username: string, hideEmail: boolean) {
		const isEmailVisible = !hideEmail
		this.send('setProfile', { avatar, username, isEmailVisible })
	}

	public getContacts() {
		this.send('getContacts')
	}

	public getProfile() {
		this.send('getProfile')
	}

	public addContact(contactId: number) {
		this.send('addContact', { contactId })
	}

	public deleteContact(contactId: number) {
		this.send('deleteContact', { contactId })
	}

	public getUsers() {
		this.send('getUsers')
	}

	public getChatMessages(contactId: number, isGroup: boolean) {
		this.send('getChatMessages', { contactId, isGroup })
	}

	public sendMessage(receiverId: number, message: string, isGroup: boolean) {
		this.send('sendMessage', { receiverId, message, isGroup })
	}

	public editMessage(messageId: number, message: string) {
		this.send('editMessage', { messageId, message })
	}

	public deleteMessage(messageId: number) {
		this.send('deleteMessage', { messageId })
	}

	public createGroup(groupName: string) {
		this.send('createGroup', { groupName })
	}

	public addGroupMember(groupId: number, memberId: number) {
		this.send('addGroupMember', { groupId, memberId })
	}
}

const ws = new WebSocketAPI()

export default ws
