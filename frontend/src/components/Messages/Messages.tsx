import { useEffect, useRef, useState } from 'react'
import wsApi from '../../utils/websocketApi'
import Message from '../Message/Message'
//@ts-ignore
import classes from './Messages.module.css'

export function Messages(props: any) {
	const [messages, setMessages] = useState<any[]>([])

	const messagesEndRef = useRef<HTMLDivElement>(null)
	const isFirstRender = useRef(true)

	useEffect(() => {
		messagesEndRef.current?.scrollIntoView({
			behavior: isFirstRender.current ? 'auto' : 'smooth',
		})
		isFirstRender.current = false
	}, [messages])

	useEffect(() => {
		wsApi.on('getChatMessages', data => {
			if (Array.isArray(data)) {
				setMessages(data)
			} else {
				setMessages([])
			}
		})
	}, [])

	useEffect(() => {
		const handleNewMessage = (data: any) => {
			if (data && typeof data === 'object') {
				setMessages(prevMessages => [...prevMessages, data])
				console.log(data)
				console.log(messages)
			} else {
				console.error('sendMessages event returned invalid data:', data)
			}
		}

		wsApi.on('messageSent', handleNewMessage)
	}, [])

	return (
		<div className={classes.Messages}>
			{messages.map(message => (
				<Message
					key={message.id}
					onContextMenu={(e: Event) => {
						e.preventDefault()
						console.log('rclick')
					}}
					{...message}
				/>
			))}
			<div ref={messagesEndRef} />
		</div>
	)
}

export default Messages
