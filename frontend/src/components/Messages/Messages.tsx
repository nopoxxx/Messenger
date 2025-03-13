import { useEffect, useRef } from 'react'
import Message from '../Message/Message'
//@ts-ignore
import classes from './Messages.module.css'

const messages = Array.from({ length: 100 }, (_, i) => {
	const authorId = Math.random() < 0.5 ? 1 : 2
	const recipientId = authorId === 1 ? 2 : 1
	return {
		id: i + 1,
		text: `Сообщение ${i + 1}`,
		date: new Date().toLocaleString(),
		authorId,
		recipientId,
	}
})

export function Messages() {
	const messagesEndRef = useRef<HTMLDivElement>(null)
	const isFirstRender = useRef(true)

	useEffect(() => {
		messagesEndRef.current?.scrollIntoView({
			behavior: isFirstRender.current ? 'auto' : 'smooth',
		})
		isFirstRender.current = false
	}, [messages])

	return (
		<div className={classes.Messages}>
			{messages.map(message => (
				<Message key={message.id} {...message} />
			))}
			<div ref={messagesEndRef} />
		</div>
	)
}

export default Messages
